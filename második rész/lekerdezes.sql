WITH latest_prices AS (
    SELECT 
        ph.product_id,
        ph.price,
        ph.updated_at
    FROM price_history ph
    INNER JOIN (
        SELECT 
            product_id,
            MAX(updated_at) AS latest_updated_at
        FROM price_history
        WHERE updated_at <= '2024-11-14'
        GROUP BY product_id
    ) AS lp ON ph.product_id = lp.product_id AND ph.updated_at = lp.latest_updated_at
),
package_prices AS (
    SELECT 
        ppc.product_package_id AS package_id,
        SUM(lp.price * ppc.quantity) AS package_price
    FROM product_package_contents ppc
    INNER JOIN latest_prices lp ON ppc.product_id = lp.product_id
    GROUP BY ppc.product_package_id
)
SELECT 
    pp.id AS package_id,
    pp.title AS package_name,
    pp_calc.package_price AS calculated_price
FROM product_packages pp
INNER JOIN package_prices pp_calc ON pp.id = pp_calc.package_id
WHERE pp.id = 1;
