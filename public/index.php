<?php
declare(strict_types=1);

mb_internal_encoding('UTF-8');
header('Content-Type: text/html; charset=UTF-8');

/**
 * الصفحة الرئيسية للمتجر (RTL + واجهة نيون).
 *
 * ملاحظة: هذه النسخة الأولى المطلوبة للملف index.php فقط.
 * سيتم لاحقاً نقل الإعدادات والوظائف إلى ملفات /core المنفصلة.
 */

const DB_HOST = 'sql113.infinityfree.com';
const DB_NAME = 'if0_41426156_syria';
const DB_USER = 'if0_41426156';
const DB_PASS = '6aiAnbEwbm';

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_NAME);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function ensureSchema(PDO $pdo): void
{
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS categories (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS products (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            category_id INT UNSIGNED NOT NULL,
            name VARCHAR(200) NOT NULL,
            description TEXT NULL,
            price DECIMAL(10,2) NOT NULL,
            stock INT UNSIGNED NOT NULL DEFAULT 0,
            image_path VARCHAR(255) NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id)
                ON UPDATE CASCADE ON DELETE RESTRICT,
            INDEX idx_products_category (category_id),
            INDEX idx_products_active (is_active)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
    );

    $defaultCategories = ['عطور', 'ملابس', 'إكسسوارات', 'إلكترونيات'];
    $stmt = $pdo->prepare('INSERT IGNORE INTO categories (name) VALUES (:name)');

    foreach ($defaultCategories as $categoryName) {
        $stmt->execute([':name' => $categoryName]);
    }
}

$products = [];
$errorMessage = '';

try {
    $pdo = db();
    ensureSchema($pdo);

    $stmt = $pdo->prepare(
        'SELECT p.id, p.name, p.price, p.image_path, c.name AS category_name
         FROM products p
         INNER JOIN categories c ON c.id = p.category_id
         WHERE p.is_active = 1 AND p.stock > 0
         ORDER BY p.created_at DESC
         LIMIT 24'
    );
    $stmt->execute();
    $products = $stmt->fetchAll();
} catch (Throwable $exception) {
    http_response_code(500);
    $errorMessage = 'تعذر تحميل المنتجات حالياً. يرجى المحاولة لاحقاً.';
    error_log('Index DB Error: ' . $exception->getMessage());
}
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="متجر إلكتروني احترافي بواجهة عربية RTL وتصميم نيون عصري.">
    <title>الصفحة الرئيسية | متجر سوريا</title>
    <style>
        :root {
            --bg: #070a13;
            --card: #10162b;
            --line: #28345f;
            --text: #eef4ff;
            --muted: #98a7cc;
            --neon-primary: #00e5ff;
            --neon-secondary: #b026ff;
            --danger: #ff4d8d;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Tajawal", "Cairo", "Segoe UI", Tahoma, sans-serif;
            background: radial-gradient(circle at top, #121e3f 0%, var(--bg) 55%);
            color: var(--text);
            min-height: 100vh;
        }

        .container {
            width: min(1200px, 92vw);
            margin: 0 auto;
        }

        .hero {
            padding: 3rem 0 2rem;
            text-align: center;
        }

        .logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 68px;
            height: 68px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: linear-gradient(145deg, #101c35, #0a1227);
            box-shadow: 0 0 20px rgba(0, 229, 255, 0.35), 0 0 34px rgba(176, 38, 255, 0.3);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        h1 {
            margin: 0;
            font-size: clamp(1.7rem, 2.4vw, 2.6rem);
            text-shadow: 0 0 16px rgba(0, 229, 255, 0.45);
        }

        .subtitle {
            color: var(--muted);
            margin-top: 0.85rem;
            font-size: 1.02rem;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 1rem;
            padding-bottom: 3rem;
        }

        .card {
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: linear-gradient(180deg, rgba(16, 22, 43, 0.95) 0%, rgba(10, 16, 34, 0.96) 100%);
            transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(0, 229, 255, 0.8);
            box-shadow: 0 0 16px rgba(0, 229, 255, 0.36), 0 0 28px rgba(176, 38, 255, 0.28);
        }

        .product-image-wrap {
            aspect-ratio: 1 / 1;
            background: #091128;
            border-bottom: 1px solid var(--line);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .content {
            padding: 0.95rem;
        }

        .category {
            display: inline-block;
            color: var(--neon-primary);
            font-size: 0.84rem;
            margin-bottom: 0.45rem;
        }

        .name {
            margin: 0 0 0.7rem;
            font-size: 1.04rem;
            line-height: 1.5;
            min-height: 3.1em;
        }

        .price-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.8rem;
        }

        .price {
            font-size: 1.15rem;
            font-weight: 700;
            color: #9df8ff;
            text-shadow: 0 0 10px rgba(0, 229, 255, 0.45);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.64rem 0.8rem;
            border-radius: 12px;
            border: 1px solid rgba(0, 229, 255, 0.65);
            background: linear-gradient(90deg, rgba(0, 229, 255, 0.2), rgba(176, 38, 255, 0.2));
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            transition: box-shadow 0.2s ease, transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 14px rgba(0, 229, 255, 0.4), 0 0 20px rgba(176, 38, 255, 0.3);
        }

        .empty,
        .error {
            border: 1px dashed var(--line);
            border-radius: 16px;
            text-align: center;
            padding: 1.25rem;
            margin-bottom: 2rem;
            background: rgba(16, 22, 43, 0.55);
            color: var(--muted);
        }

        .error {
            border-color: rgba(255, 77, 141, 0.45);
            color: #ffc3d8;
        }

        footer {
            border-top: 1px solid var(--line);
            text-align: center;
            padding: 1rem;
            color: var(--muted);
            font-size: 0.92rem;
            background: rgba(7, 10, 19, 0.9);
        }

        @media (max-width: 640px) {
            .hero {
                padding-top: 2rem;
            }
        }
    </style>
</head>
<body>
<main class="container">
    <section class="hero">
        <div class="logo">SY</div>
        <h1>متجر سوريا الإلكتروني</h1>
        <p class="subtitle">اكتشف منتجات مميزة بتصميم حديث وتجربة شراء سهلة وآمنة.</p>
    </section>

    <?php if ($errorMessage !== ''): ?>
        <div class="error"><?= e($errorMessage) ?></div>
    <?php endif; ?>

    <?php if (!$errorMessage && empty($products)): ?>
        <div class="empty">لا توجد منتجات متاحة حالياً. يرجى العودة لاحقاً.</div>
    <?php endif; ?>

    <section class="grid" aria-label="قائمة المنتجات">
        <?php foreach ($products as $product): ?>
            <?php
            $imagePath = !empty($product['image_path']) ? (string) $product['image_path'] : '../assets/images/placeholder.png';
            ?>
            <article class="card">
                <div class="product-image-wrap">
                    <img
                        class="product-image"
                        src="<?= e($imagePath) ?>"
                        alt="<?= e($product['name']) ?>"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
                <div class="content">
                    <span class="category"><?= e($product['category_name']) ?></span>
                    <h2 class="name"><?= e($product['name']) ?></h2>
                    <div class="price-row">
                        <span class="price"><?= number_format((float) $product['price'], 2) ?> $</span>
                    </div>
                    <a class="btn" href="product.php?id=<?= (int) $product['id'] ?>">عرض المنتج</a>
                </div>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<footer>جميع الحقوق محفوظة للبائع والمطور</footer>
</body>
</html>
