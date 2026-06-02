<?php
// models/Blog.php
// Blog Database Model for Wiloty Foundation

require_once __DIR__ . '/../config/db.php';

class Blog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Insert a new blog post
    public function create($title, $summary, $content, $image_url = null, $is_featured = 0) {
        // If this post is set as featured, reset others if needed, or maintain multiple featured
        $stmt = $this->db->prepare("INSERT INTO blogs (title, summary, content, image_url, is_featured) VALUES (:title, :summary, :content, :image_url, :is_featured)");
        $result = $stmt->execute([
            'title' => $title,
            'summary' => $summary,
            'content' => $content,
            'image_url' => $image_url,
            'is_featured' => $is_featured ? 1 : 0
        ]);

        if ($result) {
            $blog_id = $this->db->lastInsertId();
            $blog_link = defined('SITE_URL') ? SITE_URL . "/blog-detail.php?id=" . $blog_id : "https://wilotyfoundation.org/blog-detail.php?id=" . $blog_id;

            // Send notification to subscribers
            require_once __DIR__ . '/MailingList.php';
            $ml = new MailingList();
            $emails = $ml->getSubscriberEmails();

            if (!empty($emails)) {
                $subject = "New Blog: " . $title;
                
                // Structured HTML Email Body
                $body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; color: #333; border: 1px solid #eaeaea; border-radius: 8px; padding: 20px;'>
                    <div style='text-align: center; padding-bottom: 15px; border-bottom: 2px dashed #eaeaea; margin-bottom: 20px;'>
                        <h2 style='margin: 0; color: #ff6b00;'>Wiloty Foundation</h2>
                        <p style='margin: 5px 0 0 0; font-size: 14px; color: #666;'>Empowering Communities Through Impact</p>
                    </div>
                    
                    <h3 style='color: #111;'>{$title}</h3>
                    <p style='font-size: 15px; line-height: 1.6;'>We just published a new article. Check it out below:</p>
                    
                    <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #ff6b00; margin: 20px 0; font-style: italic; color: #555;'>
                        {$summary}
                    </div>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$blog_link}' style='background: #ff6b00; color: #fff; text-decoration: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; display: inline-block;'>👉 Read Full Article</a>
                    </div>
                    
                    <div style='border-top: 2px dashed #eaeaea; padding-top: 20px; margin-top: 20px; text-align: center; font-size: 12px; color: #999;'>
                        <p>You are receiving this email because you subscribed to Wiloty Foundation updates.</p>
                        <p><a href='mailto:info@wilotyfoundation.org' style='color: #999;'>info@wilotyfoundation.org</a></p>
                    </div>
                </div>";

                foreach ($emails as $email) {
                    send_email($email, $subject, $body, 'info');
                }
            }
        }

        return $result;
    }

    // Update an existing blog post
    public function update($id, $title, $summary, $content, $image_url = null, $is_featured = 0) {
        if ($image_url !== null) {
            $stmt = $this->db->prepare("UPDATE blogs SET title = :title, summary = :summary, content = :content, image_url = :image_url, is_featured = :is_featured WHERE id = :id");
            $params = [
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'image_url' => $image_url,
                'is_featured' => $is_featured ? 1 : 0,
                'id' => $id
            ];
        } else {
            $stmt = $this->db->prepare("UPDATE blogs SET title = :title, summary = :summary, content = :content, is_featured = :is_featured WHERE id = :id");
            $params = [
                'title' => $title,
                'summary' => $summary,
                'content' => $content,
                'is_featured' => $is_featured ? 1 : 0,
                'id' => $id
            ];
        }
        return $stmt->execute($params);
    }

    // Retrieve a single blog post by ID
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM blogs WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    // Retrieve featured blogs (used for automatic hero carousel rotation)
    public function getFeatured($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM blogs WHERE is_featured = 1 ORDER BY updated_at DESC LIMIT :limit");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Retrieve all blogs with pagination
    public function getAll($limit = 10, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM blogs ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Count total blogs
    public function count() {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM blogs");
        return $stmt->fetch()['total'] ?? 0;
    }

    // Delete a blog post
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM blogs WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }
}
