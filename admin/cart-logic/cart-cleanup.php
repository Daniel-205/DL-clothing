<?php
// this  script  runs every 30 days to remove abandoned carts

require_once '../../includes/dbconfig.php'; 


$log = [];

try {
   
    $threshold = date('Y-m-d H:i:s', strtotime('-40 days'));

    
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE updated_at < ?");
    $stmt->bind_param("s", $threshold);

    if ($stmt->execute()) {
        $deletedRows = $stmt->affected_rows;
        $log[] = " Cleanup successful: $deletedRows abandoned cart items removed.";
    } else {
        $log[] = " Failed to clean up abandoned carts: " . $stmt->error;
    }

    $stmt->close();
} catch (Exception $e) {
    $log[] = " Exception occurred: " . $e->getMessage();
}


foreach ($log as $entry) {
    echo $entry . PHP_EOL;
}
?>
