<?php
require_once '../config/database.php';

if(!isAdmin()) {
    exit('Unauthorized');
}

if(!isset($_POST['user_id'])) {
    exit('Missing user ID');
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_POST['user_id'];

// Get customer details
$query = "SELECT * FROM users WHERE id = :user_id AND role = 'customer'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();

if($stmt->rowCount() == 0) {
    echo '<div class="alert alert-danger">ไม่พบข้อมูลลูกค้า</div>';
    exit();
}

$customer = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order statistics
$stats_query = "SELECT 
                COUNT(*) as total_orders,
                COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_orders,
                COALESCE(SUM(total_amount), 0) as total_spent,
                COALESCE(AVG(total_amount), 0) as avg_order,
                MAX(created_at) as last_order_date
                FROM orders 
                WHERE user_id = :user_id";
$stats_stmt = $db->prepare($stats_query);
$stats_stmt->bindParam(':user_id', $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->fetch(PDO::FETCH_ASSOC);

// Get recent orders
$orders_query = "SELECT order_number, total_amount, created_at, order_status 
                 FROM orders 
                 WHERE user_id = :user_id 
                 ORDER BY created_at DESC 
                 LIMIT 5";
$orders_stmt = $db->prepare($orders_query);
$orders_stmt->bindParam(':user_id', $user_id);
$orders_stmt->execute();
$recent_orders = $orders_stmt->fetchAll(PDO::FETCH_ASSOC);

$initial = strtoupper(substr($customer['first_name'], 0, 1) . substr($customer['last_name'], 0, 1));
?>

<div class="customer-detail">
    <div class="customer-detail-avatar">
        <?php echo $initial ?: 'U'; ?>
    </div>
    
    <h4 class="text-center mb-3"><?php echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']); ?></h4>
    
    <div class="detail-row">
        <div class="detail-label">Username</div>
        <div class="detail-value">@<?php echo htmlspecialchars($customer['username']); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">อีเมล</div>
        <div class="detail-value"><?php echo htmlspecialchars($customer['email']); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">เบอร์โทร</div>
        <div class="detail-value"><?php echo htmlspecialchars($customer['phone'] ?: 'ไม่ได้ระบุ'); ?></div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">ที่อยู่</div>
        <div class="detail-value">
            <?php 
            $address = [];
            if($customer['address']) $address[] = $customer['address'];
            if($customer['city']) $address[] = $customer['city'];
            if($customer['postal_code']) $address[] = $customer['postal_code'];
            if($customer['country']) $address[] = $customer['country'];
            
            echo !empty($address) ? htmlspecialchars(implode(', ', $address)) : 'ไม่ได้ระบุ';
            ?>
        </div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">วันที่สมัคร</div>
        <div class="detail-value">
            <?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?>
        </div>
    </div>
    
    <div class="detail-row">
        <div class="detail-label">สถานะ</div>
        <div class="detail-value">
            <span class="status-badge status-<?php echo $customer['status']; ?>">
                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                <?php echo $customer['status'] == 'active' ? 'ใช้งาน' : 'ปิดใช้งาน'; ?>
            </span>
        </div>
    </div>
    
    <hr class="my-4">
    
    <h6 class="fw-bold mb-3">สถิติการสั่งซื้อ</h6>
    
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="bg-light p-3 rounded text-center">
                <div class="text-muted small">คำสั่งซื้อทั้งหมด</div>
                <div class="fw-bold fs-4"><?php echo $stats['total_orders']; ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light p-3 rounded text-center">
                <div class="text-muted small">สำเร็จแล้ว</div>
                <div class="fw-bold fs-4 text-success"><?php echo $stats['completed_orders']; ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light p-3 rounded text-center">
                <div class="text-muted small">ยอดใช้จ่ายรวม</div>
                <div class="fw-bold fs-4 text-primary">฿<?php echo number_format($stats['total_spent'], 2); ?></div>
            </div>
        </div>
        <div class="col-6">
            <div class="bg-light p-3 rounded text-center">
                <div class="text-muted small">เฉลี่ยต่อครั้ง</div>
                <div class="fw-bold fs-4 text-info">฿<?php echo number_format($stats['avg_order'], 2); ?></div>
            </div>
        </div>
    </div>
    
    <?php if(!empty($recent_orders)): ?>
    <h6 class="fw-bold mb-3">คำสั่งซื้อล่าสุด</h6>
    <div class="table-responsive">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>เลขที่ออเดอร์</th>
                    <th>วันที่</th>
                    <th>ยอดเงิน</th>
                    <th>สถานะ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($recent_orders as $order): ?>
                <tr>
                    <td><small>#<?php echo $order['order_number']; ?></small></td>
                    <td><small><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></small></td>
                    <td><small class="text-primary">฿<?php echo number_format($order['total_amount'], 2); ?></small></td>
                    <td>
                        <span class="order-status status-<?php echo $order['order_status']; ?>" style="font-size: 0.7rem;">
                            <?php 
                                $status_th = [
                                    'pending' => 'รอดำเนินการ',
                                    'processing' => 'กำลังดำเนินการ',
                                    'shipped' => 'จัดส่งแล้ว',
                                    'delivered' => 'ได้รับสินค้า',
                                    'cancelled' => 'ยกเลิก'
                                ];
                                echo $status_th[$order['order_status']] ?? $order['order_status'];
                            ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<style>
    .customer-detail {
        padding: 1rem;
    }
    
    .customer-detail-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 600;
        margin: 0 auto 1rem;
    }
    
    .detail-row {
        display: flex;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detail-label {
        width: 100px;
        color: var(--secondary);
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .detail-value {
        flex: 1;
        color: var(--dark);
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .status-badge {
        display: inline-block;
        padding: 0.2rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .status-active {
        background: #dcfce7;
        color: #16a34a;
    }
    
    .status-inactive {
        background: #fee2e2;
        color: #dc2626;
    }
    
    .order-status {
        display: inline-block;
        padding: 0.2rem 0.5rem;
        border-radius: 12px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    
    .status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .status-processing {
        background: #cce5ff;
        color: #004085;
    }
    
    .status-shipped {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .status-delivered {
        background: #d4edda;
        color: #155724;
    }
    
    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
</style>