<?php
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json; charset=utf-8');

function chatResponse(string $reply, array $data = []): void {
    echo json_encode([
        'success' => true,
        'reply' => $reply,
        'data' => $data,
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function chatError(string $reply, int $statusCode = 400): void {
    http_response_code($statusCode);
    echo json_encode([
        'success' => false,
        'reply' => $reply,
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

function chatNormalize(string $text): string {
    $text = mb_strtolower(trim($text), 'UTF-8');
    $map = [
        'à' => 'a', 'á' => 'a', 'ạ' => 'a', 'ả' => 'a', 'ã' => 'a',
        'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ậ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a',
        'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ặ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a',
        'è' => 'e', 'é' => 'e', 'ẹ' => 'e', 'ẻ' => 'e', 'ẽ' => 'e',
        'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ệ' => 'e', 'ể' => 'e', 'ễ' => 'e',
        'ì' => 'i', 'í' => 'i', 'ị' => 'i', 'ỉ' => 'i', 'ĩ' => 'i',
        'ò' => 'o', 'ó' => 'o', 'ọ' => 'o', 'ỏ' => 'o', 'õ' => 'o',
        'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ộ' => 'o', 'ổ' => 'o', 'ỗ' => 'o',
        'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ợ' => 'o', 'ở' => 'o', 'ỡ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'ụ' => 'u', 'ủ' => 'u', 'ũ' => 'u',
        'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ự' => 'u', 'ử' => 'u', 'ữ' => 'u',
        'ỳ' => 'y', 'ý' => 'y', 'ỵ' => 'y', 'ỷ' => 'y', 'ỹ' => 'y',
        'đ' => 'd',
    ];

    return strtr($text, $map);
}

function chatHasAny(string $text, array $keywords): bool {
    foreach ($keywords as $keyword) {
        if (strpos($text, chatNormalize($keyword)) !== false) {
            return true;
        }
    }

    return false;
}

function chatMoney($value): string {
    return number_format((float)$value, 0, ',', '.') . ' VNĐ';
}

function chatLabel(string $value, array $labels): string {
    return $labels[$value] ?? $value;
}

function chatCarStatus(string $status): string {
    return chatLabel($status, [
        'Available' => 'Còn trống',
        'Booked' => 'Đã được đặt',
        'Maintenance' => 'Đang bảo trì',
    ]);
}

function chatBookingStatus(string $status): string {
    return chatLabel($status, [
        'Pending' => 'Chờ xác nhận',
        'Confirmed' => 'Đã xác nhận',
        'Paid' => 'Đã thanh toán',
        'Completed' => 'Hoàn tất',
        'Cancelled' => 'Đã hủy',
    ]);
}

function chatPaymentStatus(string $status): string {
    return chatLabel($status, [
        'Pending' => 'Chờ thanh toán',
        'Paid' => 'Đã thanh toán',
        'Failed' => 'Thất bại',
        'Cancelled' => 'Đã hủy',
    ]);
}

function chatPaymentType(string $type): string {
    return chatLabel($type, [
        'Deposit' => 'Tiền cọc',
        'Rental' => 'Tiền thuê xe',
        'Final' => 'Thanh toán cuối',
        'Penalty' => 'Tiền phạt',
        'Refund' => 'Hoàn tiền',
    ]);
}

function chatTransmission(string $transmission): string {
    return chatLabel($transmission, [
        'Automatic' => 'Số tự động',
        'Manual' => 'Số sàn',
    ]);
}

function chatFuelType(string $fuelType): string {
    return chatLabel($fuelType, [
        'Gasoline' => 'Xăng',
        'Diesel' => 'Dầu',
        'Electric' => 'Điện',
        'Hybrid' => 'Hybrid',
    ]);
}

function chatCarPayload(array $car): array {
    return [
        'id' => (int)$car['CarID'],
        'name' => $car['CarName'],
        'price' => (float)$car['PricePerDay'],
        'priceText' => chatMoney($car['PricePerDay']) . '/ngày',
        'depositText' => chatMoney($car['DepositAmount']),
        'status' => chatCarStatus((string)$car['Status']),
        'transmission' => chatTransmission((string)$car['Transmission']),
        'fuelType' => chatFuelType((string)$car['FuelType']),
        'seats' => (int)$car['Seats'],
        'location' => ((string)($car['Location'] ?? '') !== '0') ? (string)($car['Location'] ?? '') : '',
        'image' => !empty($car['MainImage']) ? '/Carrental/CarRental_Frontend/assets/img/cars/' . ltrim($car['MainImage'], '/\\') : '/Carrental/CarRental_Frontend/assets/img/cars/car-1.png',
        'url' => '/Carrental/vehicle/' . (int)$car['CarID'],
    ];
}

function chatBookingPayload(array $booking): array {
    return [
        'id' => (int)$booking['BookingID'],
        'title' => 'Đơn #' . (int)$booking['BookingID'] . ' - ' . $booking['CarName'],
        'dateText' => $booking['StartDate'] . ' đến ' . $booking['EndDate'],
        'daysText' => (int)$booking['RentalDays'] . ' ngày',
        'priceText' => chatMoney($booking['TotalPrice']),
        'status' => chatBookingStatus((string)$booking['Status']),
        'url' => '/Carrental/CarRental_Frontend/my-bookings.php',
    ];
}

function chatPaymentPayload(array $payment): array {
    return [
        'id' => (int)$payment['PaymentID'],
        'title' => 'Thanh toán #' . (int)$payment['PaymentID'] . ' - ' . $payment['CarName'],
        'bookingText' => 'Đơn #' . (int)$payment['BookingID'],
        'type' => chatPaymentType((string)$payment['PaymentType']),
        'amountText' => chatMoney($payment['Amount']),
        'status' => chatPaymentStatus((string)$payment['Status']),
        'url' => '/Carrental/CarRental_Frontend/my-payments.php',
    ];
}

function chatPriceLimit(string $text): ?float {
    if (!preg_match('/(\d+(?:[.,]\d+)?)\s*(trieu|triệu|m|k|nghin|ngàn|ngan|vnd|dong)?/iu', $text, $matches)) {
        return null;
    }

    $number = (float)str_replace(',', '.', $matches[1]);
    $unit = chatNormalize($matches[2] ?? '');

    if ($unit === 'trieu' || $unit === 'm') {
        return $number * 1000000;
    }

    if ($unit === 'k' || $unit === 'nghin' || $unit === 'ngan') {
        return $number * 1000;
    }

    return $number >= 10000 ? $number : null;
}

function chatRows(mysqli_result $result): array {
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }

    return $rows;
}

$rawInput = file_get_contents('php://input');
$payload = json_decode($rawInput, true);
$question = trim((string)($payload['message'] ?? $_POST['message'] ?? ''));

if ($question === '') {
    chatError('Bạn vui lòng nhập câu hỏi để mình hỗ trợ.');
}

$normalized = chatNormalize($question);
$userID = (int)($_SESSION['user_id'] ?? 0);

if (chatHasAny($normalized, ['xin chao', 'hello', 'hi', 'chao', 'alo', 'hey', 'good morning', 'good afternoon', 'good evening'])) {
    $greetings = [
        'Xin chào! Mình là trợ lý AI của VinaDrive. Bạn muốn tìm xe, hỏi giá thuê hay kiểm tra đơn đặt xe?',
        'Chào bạn! Mình có thể hỗ trợ về xe còn trống, giá thuê, giấy tờ, đặt xe và thanh toán.',
        'VinaDrive xin chào! Bạn cứ đặt câu hỏi, mình sẽ tra dữ liệu website để hỗ trợ nhanh nhất có thể.',
    ];
    chatResponse($greetings[array_rand($greetings)]);
}

if (chatHasAny($normalized, ['cam on', 'thank', 'thanks', 'thank you', 'tot qua', 'hay qua'])) {
    $thanks = [
        'Rất vui được hỗ trợ bạn. Nếu cần tìm xe hoặc kiểm tra đơn, bạn cứ hỏi mình nhé.',
        'Không có gì ạ. Mình luôn sẵn sàng hỗ trợ bạn về dịch vụ thuê xe của VinaDrive.',
        'Cảm ơn bạn. Chúc bạn chọn được chiếc xe phù hợp!',
    ];
    chatResponse($thanks[array_rand($thanks)]);
}

if (chatHasAny($normalized, ['tam biet', 'bye', 'goodbye', 'hen gap lai', 'gap lai sau'])) {
    $goodbyes = [
        'Tạm biệt bạn. Khi cần thuê xe, VinaDrive luôn sẵn sàng hỗ trợ!',
        'Hẹn gặp lại bạn. Chúc bạn một ngày thuận lợi!',
        'Cảm ơn bạn đã ghé VinaDrive. Mình sẽ ở đây khi bạn cần hỗ trợ thêm.',
    ];
    chatResponse($goodbyes[array_rand($goodbyes)]);
}

if (chatHasAny($normalized, ['ban la ai', 'm la ai', 'chatbot la ai', 'tro ly la ai', 'ai day'])) {
    chatResponse('Mình là trợ lý AI của VinaDrive. Mình có thể trả lời câu hỏi cơ bản và tra dữ liệu website như danh sách xe, giá thuê, xe còn trống, đơn đặt xe và thanh toán của bạn.');
}

if (chatHasAny($normalized, ['ban lam duoc gi', 'co the giup gi', 'giup toi', 'ho tro gi', 'huong dan toi', 'help'])) {
    chatResponse("Mình có thể hỗ trợ bạn các nội dung sau:\n- Tìm xe theo giá, số ghế, hãng, hộp số, nhiên liệu\n- Kiểm tra xe còn trống\n- Hướng dẫn đặt xe, giấy tờ, thanh toán\n- Xem đơn đặt xe và khoản thanh toán của bạn khi đã đăng nhập\nBạn có thể hỏi ví dụ: \"Có xe 7 ghế còn trống không?\" hoặc \"Xe dưới 1 triệu\".");
}

if (chatHasAny($normalized, ['khong hieu', 'noi lai', 'ban noi gi', 'la sao', 'toi khong ro'])) {
    chatResponse('Mình xin lỗi nếu câu trả lời chưa rõ. Bạn có thể hỏi ngắn gọn theo mẫu như: "xe còn trống", "xe 7 ghế", "xe dưới 1 triệu", "đơn của tôi" hoặc "thanh toán của tôi".');
}

if (chatHasAny($normalized, ['xin loi', 'sorry'])) {
    chatResponse('Không sao đâu ạ. Bạn cứ hỏi tự nhiên, mình sẽ cố gắng hỗ trợ trong phạm vi dữ liệu của VinaDrive.');
}

if (chatHasAny($normalized, ['lien he', 'hotline', 'so dien thoai', 'dia chi', 'email'])) {
    chatResponse('Bạn có thể liên hệ VinaDrive qua hotline +01234567890, email example@gmail.com hoặc đến địa chỉ 20 Nguyễn Duy Trinh, Nghệ An.');
}

if (chatHasAny($normalized, ['giay to', 'cccd', 'cmnd', 'can cuoc', 'bang lai', 'gplx'])) {
    chatResponse('Để thuê xe, bạn nên chuẩn bị CCCD/CMND, giấy phép lái xe phù hợp và thông tin liên hệ. Nếu thuê xe tự lái, hãy cập nhật ảnh GPLX trong hồ sơ để việc xác minh nhanh hơn.');
}

if (chatHasAny($normalized, ['cach dat', 'dat xe nhu the nao', 'lam sao de dat', 'huong dan dat', 'dat xe'])) {
    chatResponse('Bạn vào trang Thuê xe, chọn xe phù hợp, bấm Xem chi tiết rồi chọn Đặt xe. Sau khi gửi đơn, bạn theo dõi trang Đơn đặt xe của tôi và Thanh toán của tôi.');
}

if (chatHasAny($normalized, ['thanh toan nhu the nao', 'hinh thuc thanh toan', 'chuyen khoan', 'tien mat', 'momo'])) {
    chatResponse('Website đang theo dõi thanh toán trong mục Thanh toán của tôi. Khoản cọc, tiền thuê hoặc thanh toán cuối sẽ có trạng thái Chờ thanh toán/Đã thanh toán để bạn kiểm tra.');
}

if (chatHasAny($normalized, ['don cua toi', 'don dat xe cua toi', 'lich su dat', 'booking cua toi'])) {
    if ($userID <= 0) {
        chatResponse('Bạn cần đăng nhập để mình xem đơn đặt xe của bạn. Sau khi đăng nhập, hãy hỏi lại "đơn của tôi".');
    }

    $stmt = $conn->prepare("
        SELECT b.BookingID, b.StartDate, b.EndDate, b.RentalDays, b.TotalPrice, b.Status, b.ReturnStatus, c.CarName
        FROM bookings b
        INNER JOIN cars c ON b.CarID = c.CarID
        WHERE b.UserID = ?
        ORDER BY b.BookingID DESC
        LIMIT 5
    ");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $bookings = chatRows($stmt->get_result());

    if (!$bookings) {
        chatResponse('Bạn chưa có đơn đặt xe nào trên hệ thống.');
    }

    chatResponse(
        count($bookings) === 1 ? 'Đây là đơn gần nhất của bạn:' : 'Đây là các đơn gần nhất của bạn:',
        [
            'type' => 'bookings',
            'items' => array_map('chatBookingPayload', $bookings),
            'bookings' => $bookings,
        ]
    );
}

if (chatHasAny($normalized, ['thanh toan cua toi', 'hoa don cua toi', 'khoan thanh toan', 'no tien', 'chua thanh toan'])) {
    if ($userID <= 0) {
        chatResponse('Bạn cần đăng nhập để mình xem các khoản thanh toán của bạn.');
    }

    $stmt = $conn->prepare("
        SELECT p.PaymentID, p.BookingID, p.Amount, p.PaymentType, p.Status, c.CarName
        FROM payments p
        INNER JOIN bookings b ON p.BookingID = b.BookingID
        INNER JOIN cars c ON b.CarID = c.CarID
        WHERE b.UserID = ?
        ORDER BY p.PaymentID DESC
        LIMIT 5
    ");
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $payments = chatRows($stmt->get_result());

    if (!$payments) {
        chatResponse('Bạn chưa có khoản thanh toán nào trên hệ thống.');
    }

    chatResponse(
        count($payments) === 1 ? 'Đây là khoản thanh toán gần nhất của bạn:' : 'Đây là các khoản thanh toán gần nhất của bạn:',
        [
            'type' => 'payments',
            'items' => array_map('chatPaymentPayload', $payments),
            'payments' => $payments,
        ]
    );
}

$isCarQuestion = chatHasAny($normalized, [
    'xe', 'oto', 'o to', 'gia', 'bao nhieu', 'con trong', 'san co', 'available',
    'so ghe', 'ghe', 'tu dong', 'so san', 'xang', 'dau', 'dien', 'hybrid'
]);

if ($isCarQuestion) {
    $conditions = [];
    $params = [];
    $types = '';
    $limit = 6;

    if (chatHasAny($normalized, ['con trong', 'san co', 'available', 'dang co', 'co xe nao'])) {
        $conditions[] = "c.Status = 'Available'";
    }

    if (preg_match('/(\d+)\s*(ghe|cho|chỗ)/iu', $question, $seatMatches)) {
        $conditions[] = 'c.Seats >= ?';
        $params[] = (int)$seatMatches[1];
        $types .= 'i';
    }

    $priceLimit = null;
    if (chatHasAny($normalized, ['duoi', 'nho hon', 're hon', 'khong qua', 'tam gia'])) {
        $priceLimit = chatPriceLimit($question);
    }

    if ($priceLimit !== null) {
        $conditions[] = 'c.PricePerDay <= ?';
        $params[] = $priceLimit;
        $types .= 'd';
    }

    if (chatHasAny($normalized, ['tu dong', 'automatic'])) {
        $conditions[] = "LOWER(c.Transmission) LIKE '%auto%'";
    } elseif (chatHasAny($normalized, ['so san', 'manual'])) {
        $conditions[] = "LOWER(c.Transmission) LIKE '%manual%'";
    }

    $fuelMap = [
        'xang' => ['gasoline', 'xang'],
        'dau' => ['diesel', 'dau'],
        'dien' => ['electric', 'dien'],
        'hybrid' => ['hybrid'],
    ];

    foreach ($fuelMap as $fuelKeyword => $fuelValues) {
        if (chatHasAny($normalized, [$fuelKeyword])) {
            $conditions[] = 'LOWER(c.FuelType) LIKE ?';
            $params[] = '%' . $fuelValues[0] . '%';
            $types .= 's';
            break;
        }
    }

    $brandResult = $conn->query('SELECT BrandID, BrandName FROM brands ORDER BY BrandName ASC');
    if ($brandResult) {
        while ($brand = $brandResult->fetch_assoc()) {
            if ($brand['BrandName'] !== '' && strpos($normalized, chatNormalize($brand['BrandName'])) !== false) {
                $conditions[] = 'c.BrandID = ?';
                $params[] = (int)$brand['BrandID'];
                $types .= 'i';
                break;
            }
        }
    }

    $sql = "
        SELECT c.CarID, c.CarName, c.PricePerDay, c.DepositAmount, c.Status, c.Transmission, c.FuelType, c.Seats, c.Location, c.MainImage
        FROM cars c
    ";
    if ($conditions) {
        $sql .= ' WHERE ' . implode(' AND ', $conditions);
    }
    $sql .= ' ORDER BY c.Status = "Available" DESC, c.PricePerDay ASC, c.CarID DESC LIMIT ' . $limit;

    $stmt = $conn->prepare($sql);
    if ($params) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $cars = chatRows($stmt->get_result());

    if (!$cars) {
        chatResponse('Mình chưa tìm thấy xe phù hợp với câu hỏi của bạn. Bạn có thể thử hỏi theo tên xe, hãng xe, số ghế, giá/ngày hoặc trạng thái còn trống.');
    }

    $prefix = 'Mình tìm thấy các xe phù hợp trong hệ thống:';
    if (count($cars) === 1) {
        $prefix = 'Mình tìm thấy xe phù hợp trong hệ thống:';
    }

    chatResponse(
        $prefix,
        [
            'type' => 'cars',
            'items' => array_map('chatCarPayload', $cars),
            'cars' => $cars,
        ]
    );
}

chatResponse('Mình hiện có thể trả lời dựa trên dữ liệu website về xe, giá thuê, xe còn trống, đơn đặt xe, thanh toán, giấy tờ và liên hệ. Bạn thử hỏi: "xe 7 ghế còn trống", "xe dưới 1 triệu", "đơn của tôi" hoặc "thanh toán của tôi".');
