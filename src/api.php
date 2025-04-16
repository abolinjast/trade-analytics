<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $db = DB::connect();
    
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            $stmt = $db->query("SELECT * FROM trades");
            $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['status' => 'success', 'data' => $trades]);
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            
            if(!isset($input['symbol'], $input['profit_loss'], $input['trade_date'])) {
                throw new Exception('Missing required fields');
            }

            $stmt = $db->prepare("
                INSERT INTO trades 
                (symbol, profit_loss, trade_date, timeframe, position_type)
                VALUES (:symbol, :profit_loss, :trade_date, :timeframe, :position_type)
            ");
            
            $stmt->execute([
                ':symbol' => $input['symbol'],
                ':profit_loss' => $input['profit_loss'],
                ':trade_date' => $input['trade_date'],
                ':timeframe' => $input['timeframe'] ?? null,
                ':position_type' => $input['position_type'] ?? null
            ]);

            echo json_encode([
                'status' => 'success',
                'message' => 'Trade created',
                'id' => $db->lastInsertId()
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    }
    
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>