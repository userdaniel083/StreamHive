<?php
// StreamHive - Video Model
// Klasse voor video database operaties

class VideoModel {
    
    
    // Upload nieuwe video
    public function uploadVideo($userId, $title, $description, $filePath, $thumbnail) {
        return query(
            "INSERT INTO videos (user_id, title, description, file_path, thumbnail, upload_date) VALUES (?, ?, ?, ?, ?, NOW())",
            [$userId, $title, $description, $filePath, $thumbnail]
        );
    }
    
    // Haal video op via ID met uploader naam
    public function getVideoById($videoId) {
        $result = query(
            "SELECT v.*, u.name as uploader_name FROM videos v 
             JOIN users u ON v.user_id = u.id 
             WHERE v.id = ?",
            [$videoId]
        );
        return $result ? $result[0] : null;
    }
    
    // Haal alle videos op (gesorteerd op nieuwste eerst)
    public function getAllVideos() {
        return query(
            "SELECT v.*, u.name as uploader_name FROM videos v 
             JOIN users u ON v.user_id = u.id 
             ORDER BY v.upload_date DESC"
        );
    }
    
    // Haal alle videos van een gebruiker op
    public function getVideosByUser($userId) {
        return query(
            "SELECT * FROM videos WHERE user_id = ? ORDER BY upload_date DESC",
            [$userId]
        );
    }
    // Update video informatie
    public function updateVideo($videoId, $title, $description) {
        return query(
            "UPDATE videos SET title = ?, description = ? WHERE id = ?",
            [$title, $description, $videoId]
        );
    }
    

    // Verwijder video
    public function deleteVideo($videoId) {
        return query("DELETE FROM videos WHERE id = ?", [$videoId]);
    }
    
    // Zoek videos op titel of beschrijving
    public function searchVideos($searchTerm) {
        return query(
            "SELECT v.*, u.name as uploader_name FROM videos v 
             JOIN users u ON v.user_id = u.id 
             WHERE v.title LIKE ? OR v.description LIKE ? 
             ORDER BY v.upload_date DESC",
            ["%$searchTerm%", "%$searchTerm%"]
        );
    }
    
    // Verhoog aantal views voor video
    public function incrementViews($videoId) {
        return query(
            "UPDATE videos SET views = views + 1 WHERE id = ?",
            [$videoId]
        );
    }
    // Haal meest bekeken videos op
    public function getPopularVideos($limit = 10) {
        return query(
            "SELECT v.*, u.name as uploader_name FROM videos v 
             JOIN users u ON v.user_id = u.id 
             ORDER BY v.views DESC LIMIT ?",
            [$limit]
        );
    }
}

?>
