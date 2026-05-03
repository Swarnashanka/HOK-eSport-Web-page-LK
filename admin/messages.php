<?php
$adminPageTitle = 'Contact Messages';
require_once 'includes/admin-header.php';

$msg = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    execute("DELETE FROM contact_messages WHERE id = ?", [(int)$_GET['delete']], 'i');
    $msg = 'Message deleted.';
}
if (isset($_GET['mark_read']) && is_numeric($_GET['mark_read'])) {
    execute("UPDATE contact_messages SET status = 'read' WHERE id = ?", [(int)$_GET['mark_read']], 'i');
    $msg = 'Marked as read.';
}
if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
    execute("UPDATE contact_messages SET status = 'archived' WHERE id = ?", [(int)$_GET['archive']], 'i');
    $msg = 'Message archived.';
}

$filter = trim($_GET['status'] ?? '');
$allowedMsgStatuses = ['unread', 'read', 'replied', 'archived'];
$msgFilterParams = [];
if ($filter && in_array($filter, $allowedMsgStatuses)) {
    $where = 'WHERE status = ?';
    $msgFilterParams[] = $filter;
} else {
    $where = '';
    $filter = '';
}
$messages = fetchAll("SELECT * FROM contact_messages $where ORDER BY created_at DESC", $msgFilterParams);

$viewMsg = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
    $viewMsg = fetchOne("SELECT * FROM contact_messages WHERE id = ?", [(int)$_GET['view']], 'i');
    if ($viewMsg && $viewMsg['status'] === 'unread') {
        execute("UPDATE contact_messages SET status = 'read' WHERE id = ?", [$viewMsg['id']], 'i');
        $viewMsg['status'] = 'read';
    }
}

$counts = fetchOne("SELECT SUM(status='unread') as unread, SUM(status='read') as `read`, COUNT(*) as total FROM contact_messages");
?>
<?php if ($msg): ?><div class="alert alert-success" data-auto-dismiss="3000"><?= escape($msg) ?></div><?php endif; ?>

<!-- View Single Message -->
<?php if ($viewMsg): ?>
<div class="admin-card" style="max-width:700px;margin-bottom:24px;">
  <div class="admin-card-header">
    <span class="admin-card-title">Message from <?= escape($viewMsg['name']) ?></span>
    <a href="messages.php" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Close</a>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
    <?php
    $mDetails = [['fa-user','Name',$viewMsg['name']],['fa-envelope','Email','<a href="mailto:'.escape($viewMsg['email']).'">'.escape($viewMsg['email']).'</a>'],['fa-phone','Phone',escape($viewMsg['phone'] ?? 'N/A')],['fa-tag','Type',escape(str_replace('_',' ',ucfirst($viewMsg['type'])))],['fa-calendar-alt','Received',date('M j, Y H:i',strtotime($viewMsg['created_at']))],['fa-info-circle','Status',ucfirst($viewMsg['status'])]];
    foreach ($mDetails as $d): ?>
    <div><div style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:3px;"><i class="fas <?= $d[0] ?>" style="color:var(--gold);margin-right:5px;"></i><?= $d[1] ?></div><div style="color:var(--text-primary);"><?= $d[2] ?></div></div>
    <?php endforeach; ?>
  </div>
  <div style="border:1px solid var(--dark-border);border-radius:4px;padding:20px;background:var(--dark-card2);">
    <div style="font-family:var(--font-head);color:var(--gold);font-size:0.8rem;letter-spacing:1px;margin-bottom:10px;text-transform:uppercase;">Subject: <?= escape($viewMsg['subject']) ?></div>
    <p style="color:var(--text-primary);line-height:1.8;"><?= nl2br(escape($viewMsg['message'])) ?></p>
  </div>
  <div style="margin-top:16px;display:flex;gap:10px;">
    <a href="mailto:<?= escape($viewMsg['email']) ?>?subject=Re: <?= urlencode($viewMsg['subject']) ?>" class="btn btn-gold btn-sm"><i class="fas fa-reply"></i> Reply by Email</a>
    <a href="messages.php?archive=<?= $viewMsg['id'] ?>" class="btn btn-outline btn-sm"><i class="fas fa-archive"></i> Archive</a>
    <a href="messages.php?delete=<?= $viewMsg['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete this message?" style="height:36px;width:auto;padding:0 12px;border-radius:4px;display:flex;align-items:center;gap:6px;font-size:0.78rem;"><i class="fas fa-trash"></i> Delete</a>
  </div>
</div>
<?php endif; ?>

<!-- Stats -->
<div style="display:flex;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
  <a href="messages.php" class="stat-card" style="flex:1;min-width:120px;cursor:pointer;text-decoration:none;">
    <div class="stat-card-icon blue"><i class="fas fa-envelope"></i></div>
    <div><div class="stat-card-val"><?= $counts['total'] ?></div><div class="stat-card-label">Total</div></div>
  </a>
  <a href="messages.php?status=unread" class="stat-card" style="flex:1;min-width:120px;cursor:pointer;text-decoration:none;">
    <div class="stat-card-icon red"><i class="fas fa-envelope-open"></i></div>
    <div><div class="stat-card-val"><?= $counts['unread'] ?></div><div class="stat-card-label">Unread</div></div>
  </a>
  <a href="messages.php?status=read" class="stat-card" style="flex:1;min-width:120px;cursor:pointer;text-decoration:none;">
    <div class="stat-card-icon green"><i class="fas fa-check"></i></div>
    <div><div class="stat-card-val"><?= $counts['read'] ?></div><div class="stat-card-label">Read</div></div>
  </a>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <span class="admin-card-title">Messages (<?= count($messages) ?>)</span>
    <div style="display:flex;gap:8px;">
      <?php foreach ([''=> 'All','unread'=>'Unread','read'=>'Read','archived'=>'Archived'] as $val => $label): ?>
      <a href="?status=<?= $val ?>" class="btn <?= $filter === $val ? 'btn-gold' : 'btn-outline' ?> btn-sm"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php if (empty($messages)): ?>
    <div class="empty-state" style="padding:40px;"><i class="fas fa-inbox"></i><h3>No messages</h3></div>
  <?php else: ?>
  <div class="table-responsive">
    <table class="admin-table">
      <thead><tr><th>From</th><th>Subject</th><th>Type</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($messages as $m): ?>
        <tr style="<?= $m['status']==='unread'?'background:rgba(200,169,81,0.04);':'' ?>">
          <td>
            <div style="font-weight:<?= $m['status']==='unread'?'700':'400' ?>;font-size:0.85rem;"><?= escape($m['name']) ?></div>
            <div style="color:var(--text-muted);font-size:0.72rem;"><?= escape($m['email']) ?></div>
          </td>
          <td style="font-size:0.85rem;"><?= escape(substr($m['subject'],0,50)) ?></td>
          <td><span class="tag" style="font-size:0.65rem;"><?= escape(str_replace('_',' ',ucfirst($m['type']))) ?></span></td>
          <td style="color:var(--text-muted);font-size:0.75rem;"><?= date('M j, Y', strtotime($m['created_at'])) ?></td>
          <td><span style="font-size:0.7rem;<?= $m['status']==='unread'?'color:var(--red-bright);':'color:var(--text-muted);' ?>"><?= ucfirst($m['status']) ?></span></td>
          <td>
            <div class="admin-actions">
              <a href="messages.php?view=<?= $m['id'] ?>" class="btn-icon btn-view"><i class="fas fa-eye"></i></a>
              <a href="mailto:<?= escape($m['email']) ?>?subject=Re: <?= urlencode($m['subject']) ?>" class="btn-icon btn-edit" title="Reply"><i class="fas fa-reply"></i></a>
              <?php if ($m['status']==='unread'): ?><a href="messages.php?mark_read=<?= $m['id'] ?>" class="btn-icon" style="border-color:rgba(0,200,83,0.3);color:#00C853;background:rgba(0,200,83,0.08);" title="Mark Read"><i class="fas fa-check"></i></a><?php endif; ?>
              <a href="messages.php?delete=<?= $m['id'] ?>" class="btn-icon btn-delete" data-confirm="Delete message?"><i class="fas fa-trash"></i></a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/admin-footer.php'; ?>
