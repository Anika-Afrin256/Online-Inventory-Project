<?php
/**
 * discount_engine.php (PDO version)
 *
 * - Buyer quiz discount: latest discountoffer for the buyer with CoinsRequired = 0 (capped at 10%)
 *
 * For each cart line (cart row joined to buys+product for the current user),
 *   line_subtotal   = product.Price * cart.Quantity
 *   merged_percent  = min(10, buyer_quiz_percent)
 *   line_discount   = round(line_subtotal * merged_percent / 100, 2)
 * Then we update:
 *   cart.Total = line_subtotal
 *   cart.Discount_Applied = line_discount  (NOTE: amount, not percent)
 *
 * Assumes:
 *  - config.php created $pdo (PDO)
 *  - cart has: CartID, BUserID, Quantity, Total, Discount_Applied
 *  - buys maps each CartID to a ProductID
 *  - product has: ProductID, Price
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Get buyer quiz discount (0..10) */
function get_buyer_quiz_discount_percent_pdo(PDO $pdo, int $userId): float {
    $sql = "SELECT DiscountPercent
            FROM discountoffer
            WHERE DUserID = ? AND COALESCE(CoinsRequired, 0) = 0
            ORDER BY OfferID DESC
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    $row = $stmt->fetch();
    if (!$row) return 0.0;
    $percent = (float)$row['DiscountPercent'];
    if ($percent < 0) $percent = 0.0;
    if ($percent > 10) $percent = 10.0;
    return $percent;
}

/** Apply quiz discounts to current user's cart lines */
function apply_discounts_to_cart_pdo(PDO $pdo, int $userId): array {
    $pdo->beginTransaction();
    try {
        $quizPercent = get_buyer_quiz_discount_percent_pdo($pdo, $userId);

        $sql = "SELECT c.CartID, c.Quantity, p.Price
                FROM cart c
                JOIN buys b ON b.CartID = c.CartID
                JOIN product p ON p.ProductID = b.ProductID
                WHERE c.BUserID = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();

        $items = [];
        $subtotal = 0.0;
        $discountTotal = 0.0;

        $upd = $pdo->prepare("UPDATE cart SET Total = ?, Discount_Applied = ? WHERE CartID = ?");

        foreach ($rows as $r) {
            $qty = (int)$r['Quantity'];
            $price = (float)$r['Price'];
            $lineSub = $qty * $price;

            $mergedPercent = $quizPercent;
            if ($mergedPercent > 10.0) $mergedPercent = 10.0;

            $lineDiscount = round($lineSub * ($mergedPercent / 100.0), 2);
            $lineFinal    = max(0.0, $lineSub - $lineDiscount);

            $upd->execute([$lineSub, $lineDiscount, (int)$r['CartID']]);

            $subtotal      += $lineSub;
            $discountTotal += $lineDiscount;

            $items[] = [
                'CartID'         => (int)$r['CartID'],
                'quantity'       => $qty,
                'price'          => $price,
                'quiz_percent'   => $quizPercent,
                'merged_percent' => $mergedPercent,
                'line_subtotal'  => $lineSub,
                'line_discount'  => $lineDiscount,
                'line_final'     => $lineFinal
            ];
        }

        $finalTotal = max(0.0, round($subtotal - $discountTotal, 2));
        $pdo->commit();

        return [
            'ok' => true,
            'items' => $items,
            'subtotal' => $subtotal,
            'discount_total' => $discountTotal,
            'final_total' => $finalTotal,
            'quiz_percent' => $quizPercent
        ];
    } catch (Throwable $e) {
        $pdo->rollBack();
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}
