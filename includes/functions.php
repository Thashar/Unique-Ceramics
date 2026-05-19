<?php
// Product functions
function get_products(array $filters = []): array {
    $sql  = 'SELECT p.*, c.name_pl AS cat_name_pl, c.name_en AS cat_name_en
             FROM products p LEFT JOIN categories c ON c.id = p.category_id
             WHERE p.active = 1';
    $params = [];

    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['featured'])) {
        $sql .= ' AND p.featured = 1';
    }
    if (!empty($filters['search'])) {
        $sql .= ' AND (p.name_pl LIKE ? OR p.name_en LIKE ? OR p.description_pl LIKE ?)';
        $s = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$s, $s, $s]);
    }
    if (!empty($filters['min_price'])) {
        $sql .= ' AND p.price >= ?';
        $params[] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $sql .= ' AND p.price <= ?';
        $params[] = $filters['max_price'];
    }

    $sort = match($filters['sort'] ?? 'newest') {
        'price_asc'  => 'p.price ASC',
        'price_desc' => 'p.price DESC',
        'name'       => 'p.name_pl ASC',
        default      => 'p.id DESC',
    };
    $sql .= " ORDER BY {$sort}";

    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int)$filters['limit'];
        if (!empty($filters['offset'])) {
            $sql .= ' OFFSET ' . (int)$filters['offset'];
        }
    }

    return db_fetch_all($sql, $params);
}

function count_products(array $filters = []): int {
    $sql  = 'SELECT COUNT(*) FROM products p WHERE p.active = 1';
    $params = [];
    if (!empty($filters['category_id'])) {
        $sql .= ' AND p.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['search'])) {
        $sql .= ' AND (p.name_pl LIKE ? OR p.name_en LIKE ?)';
        $s = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$s, $s]);
    }
    return (int)db_query($sql, $params)->fetchColumn();
}

function get_product_by_slug(string $slug): ?array {
    return db_fetch('SELECT p.*, c.name_pl AS cat_name_pl, c.name_en AS cat_name_en
                     FROM products p LEFT JOIN categories c ON c.id = p.category_id
                     WHERE p.slug = ? AND p.active = 1', [$slug]);
}

function get_product_by_id(int $id): ?array {
    return db_fetch('SELECT p.*, c.name_pl AS cat_name_pl, c.name_en AS cat_name_en
                     FROM products p LEFT JOIN categories c ON c.id = p.category_id
                     WHERE p.id = ?', [$id]);
}

function get_product_images(array $product): array {
    $imgs = json_decode($product['images'] ?? '[]', true);
    return is_array($imgs) ? $imgs : [];
}

function get_product_main_image(array $product): string {
    $imgs = get_product_images($product);
    return !empty($imgs[0]) ? $imgs[0] : 'placeholder.jpg';
}

function product_name(array $product): string {
    $lang = current_lang();
    return h($product["name_{$lang}"] ?: $product['name_pl']);
}

function product_description(array $product): string {
    $lang = current_lang();
    return $product["description_{$lang}"] ?: $product['description_pl'];
}

// Category functions
function get_categories(bool $activeOnly = true): array {
    $sql = "SELECT c.*,
        (SELECT p.images FROM products p
         WHERE p.category_id = c.id AND p.active = 1
           AND p.images IS NOT NULL AND p.images != '[]'
         ORDER BY p.featured DESC, p.stock DESC, p.id DESC
         LIMIT 1) AS fallback_images
        FROM categories c";
    if ($activeOnly) $sql .= ' WHERE c.active = 1';
    $sql .= ' ORDER BY c.sort_order ASC, c.name_pl ASC';
    $rows = db_fetch_all($sql);
    foreach ($rows as &$row) {
        $row['fallback_image'] = '';
        if (empty($row['image']) && !empty($row['fallback_images'])) {
            $imgs = json_decode($row['fallback_images'], true);
            $row['fallback_image'] = !empty($imgs) ? $imgs[0] : '';
        }
        unset($row['fallback_images']);
    }
    return $rows;
}

function get_category_by_slug(string $slug): ?array {
    return db_fetch('SELECT * FROM categories WHERE slug = ? AND active = 1', [$slug]);
}

function category_name(array $cat): string {
    $lang = current_lang();
    return h($cat["name_{$lang}"] ?: $cat['name_pl']);
}

// Order functions
function get_orders(array $filters = []): array {
    $sql    = 'SELECT * FROM orders WHERE 1=1';
    $params = [];
    if (!empty($filters['status'])) {
        $sql .= ' AND order_status = ?';
        $params[] = $filters['status'];
    }
    $sql .= ' ORDER BY created_at DESC';
    if (!empty($filters['limit'])) {
        $sql .= ' LIMIT ' . (int)$filters['limit'];
    }
    return db_fetch_all($sql, $params);
}

function get_order_by_id(int $id): ?array {
    return db_fetch('SELECT * FROM orders WHERE id = ?', [$id]);
}

function get_order_by_number(string $num): ?array {
    return db_fetch('SELECT * FROM orders WHERE order_number = ?', [$num]);
}

function get_custom_orders(array $filters = []): array {
    $sql = 'SELECT * FROM custom_orders WHERE 1=1';
    $params = [];
    if (!empty($filters['status'])) {
        $sql .= ' AND status = ?';
        $params[] = $filters['status'];
    }
    $sql .= ' ORDER BY created_at DESC';
    return db_fetch_all($sql, $params);
}

// Settings
function get_setting(string $key, string $default = ''): string {
    $row = db_fetch('SELECT value FROM settings WHERE key_name = ?', [$key]);
    return $row ? $row['value'] : $default;
}

function set_setting(string $key, string $value): void {
    db_query('INSERT INTO settings (key_name, value) VALUES (?, ?)
              ON CONFLICT(key_name) DO UPDATE SET value = excluded.value',
        [$key, $value]);
}

// Image upload
function handle_image_upload(string $field, string $subdir = 'products/'): ?string {
    if (empty($_FILES[$field]['name'])) return null;
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;

    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $finfo   = finfo_open(FILEINFO_MIME_TYPE);
    $mime    = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed)) return null;
    if ($file['size'] > 8 * 1024 * 1024) return null;

    $ext  = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $dir  = UPLOAD_DIR . $subdir;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $name = uniqid('img_', true) . '.' . $ext;
    move_uploaded_file($file['tmp_name'], $dir . $name);
    return $subdir . $name;
}

function handle_multiple_image_upload(string $field, string $subdir = 'products/'): array {
    if (empty($_FILES[$field]['name'][0])) return [];
    $uploaded = [];
    $count    = count($_FILES[$field]['name']);
    for ($i = 0; $i < $count; $i++) {
        $_FILES['_tmp']['name']     = $_FILES[$field]['name'][$i];
        $_FILES['_tmp']['type']     = $_FILES[$field]['type'][$i];
        $_FILES['_tmp']['tmp_name'] = $_FILES[$field]['tmp_name'][$i];
        $_FILES['_tmp']['error']    = $_FILES[$field]['error'][$i];
        $_FILES['_tmp']['size']     = $_FILES[$field]['size'][$i];
        $path = handle_image_upload('_tmp', $subdir);
        if ($path) $uploaded[] = $path;
    }
    return $uploaded;
}

function slugify(string $text): string {
    $text = mb_strtolower($text);
    $map  = ['ą'=>'a','ć'=>'c','ę'=>'e','ł'=>'l','ń'=>'n','ó'=>'o','ś'=>'s','ź'=>'z','ż'=>'z'];
    $text = strtr($text, $map);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}

function paginate(int $total, int $perPage, int $page): array {
    $pages = max(1, (int)ceil($total / $perPage));
    $page  = max(1, min($page, $pages));
    return [
        'total'   => $total,
        'pages'   => $pages,
        'current' => $page,
        'offset'  => ($page - 1) * $perPage,
        'limit'   => $perPage,
    ];
}
