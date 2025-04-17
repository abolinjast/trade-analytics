<?php
require_once 'db.php';

header('Content-Type: application/json');

try {
    $db = DB::connect();
    
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            handleGetRequest($db);
            break;
            
        case 'POST':
            handlePostRequest($db);
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

function handleGetRequest($db) {
    $filters = [
        'symbol' => $_GET['symbol'] ?? 'all',
        'timeframe' => $_GET['timeframe'] ?? 'all',
        'positionType' => $_GET['positionType'] ?? 'all',
        'timePeriod' => $_GET['timePeriod'] ?? 'all'
    ];

    $query = buildQuery($filters);
    $stmt = $db->prepare($query['sql']);
    $stmt->execute($query['params']);
    
    $trades = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $trades]);
}

function buildQuery($filters) {
    $sql = "SELECT * FROM trades";
    $where = [];
    $params = [];

    if ($filters['symbol'] !== 'all') {
        $where[] = "symbol = :symbol";
        $params[':symbol'] = $filters['symbol'];
    }

    if ($filters['timeframe'] !== 'all') {
        $where[] = "timeframe = :timeframe";
        $params[':timeframe'] = $filters['timeframe'];
    }

    if ($filters['positionType'] !== 'all') {
        $where[] = "position_type = :positionType";
        $params[':positionType'] = $filters['positionType'];
    }

    if ($filters['timePeriod'] !== 'all') {
        $dateMap = [
            'last_week' => '-1 week',
            'last_month' => '-1 month',
            'last_year' => '-1 year'
        ];
        $startDate = date('Y-m-d', strtotime($dateMap[$filters['timePeriod']]));
        $where[] = "trade_date >= :startDate";
        $params[':startDate'] = $startDate;
    }

    if (!empty($where)) {
        $sql .= " WHERE " . implode(" AND ", $where);
    }
    
    $sql .= " ORDER BY trade_date ASC";

    return ['sql' => $sql, 'params' => $params];
}

function handlePostRequest($db) {
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
}
?>