<?php
// api/get_featured_blogs.php
// AJAX API endpoint to retrieve featured blogs for client-side hero rotation

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Blog.php';

header("Content-Type: application/json");
send_api_cors_headers("GET");

if (!check_rate_limit('api_get_featured_blogs', 60, 60)) {
    http_response_code(429);
    echo json_encode(["success" => false, "message" => "Too many requests."]);
    exit();
}

try {
    $blogModel = new Blog();
    // Fetch top 5 recent blogs for the rotator
    $featured_blogs = $blogModel->getAll(5, 0);

    $sanitized_blogs = [];
    foreach ($featured_blogs as $blog) {
        $sanitized_blogs[] = [
            "id" => (int)$blog['id'],
            "title" => htmlspecialchars_decode($blog['title'], ENT_QUOTES),
            "summary" => htmlspecialchars_decode($blog['summary'], ENT_QUOTES),
            "content" => sanitize_rich_html($blog['content']),
            "image_url" => !empty($blog['image_url']) ? $blog['image_url'] : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 1 1\'%3E%3C/svg%3E',
            "created_at" => date("jS F, Y", strtotime($blog['created_at']))
        ];
    }

    echo json_encode([
        "success" => true,
        "blogs" => $sanitized_blogs
    ]);

} catch (Exception $e) {
    error_log("get_featured_blogs error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Unable to load featured blogs at this time."]);
}
