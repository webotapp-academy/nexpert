<?php
/**
 * Trust System Helper
 * Provides functions to log trust events and interact with the credibility system
 */

class TrustHelper {
    /**
     * Log a trust event to the database
     * 
     * @param PDO $pdo The database connection
     * @param string $eventType The type of event
     * @param int $expertId The ID of the expert involved
     * @param int|null $learnerId The ID of the learner involved (optional)
     * @param array|null $payload Additional data for the event (optional)
     * @return bool Success status
     */
    public static function logEvent($pdo, $eventType, $expertId, $learnerId = null, $payload = null) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO trust_events (event_type, expert_id, learner_id, payload, status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())
            ");
            
            $jsonPayload = $payload ? json_encode($payload) : null;
            $stmt->execute([$eventType, $expertId, $learnerId, $jsonPayload]);
            
            return true;
        } catch (PDOException $e) {
            error_log("TrustHelper Error: Failed to log event ($eventType): " . $e->getMessage());
            return false;
        }
    }
}
