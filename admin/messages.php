<?php
/**
 * admin/messages.php
 * View, search, mark-as-read and delete contact form submissions.
 */
require_once __DIR__ . '/../includes/functions.php';
require_login();

$search = clean($_GET['q'] ?? '');
$perPage = 10;
$currentPage = max(1, (int)($_GET['page'] ?? 1));

$where = '';
$params = [];
if ($search !== '') {
    $where = "WHERE name LIKE ? OR email LIKE ? OR subject LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}

$total = $pdo->prepare("SELECT COUNT(*) FROM contact_messages $where");
$total->execute($params);
$totalRows = (int)$total->fetchColumn();
$p = paginate($totalRows, $perPage, $currentPage);

$stmt = $pdo->prepare("SELECT * FROM contact_messages $where ORDER BY created_at DESC LIMIT $perPage OFFSET {$p['offset']}");
$stmt->execute($params);
$messages = $stmt->fetchAll();

$pageTitle = 'Contact Messages';
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
  <form class="d-flex" method="GET">
    <input type="text" name="q" class="form-control form-control-sm me-2" placeholder="Search messages..." value="<?= e($search) ?>">
    <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-search"></i></button>
  </form>
</div>

<div class="card shadow-sm">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead class="table-light">
        <tr><th></th><th>Name</th><th>Email</th><th>Subject</th><th>Received</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody id="msgTable">
        <?php if (!$messages): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No messages found.</td></tr>
        <?php endif; ?>
        <?php foreach ($messages as $row): ?>
        <tr id="msg-row-<?= $row['id'] ?>" class="<?= $row['is_read'] ? '' : 'fw-bold' ?>">
          <td><?= $row['is_read'] ? '<i class="bi bi-envelope-open text-muted"></i>' : '<i class="bi bi-envelope-fill text-primary"></i>' ?></td>
          <td><?= e($row['name']) ?></td>
          <td><?= e($row['email']) ?></td>
          <td>
            <a href="#" data-bs-toggle="modal" data-bs-target="#msgModal<?= $row['id'] ?>"><?= e($row['subject'] ?: '(No subject)') ?></a>
          </td>
          <td><?= format_date($row['created_at'], 'd M Y, h:i A') ?></td>
          <td class="text-end">
            <?php if (!$row['is_read']): ?>
              <button class="btn btn-sm btn-outline-success btn-mark-read" data-id="<?= $row['id'] ?>"><i class="bi bi-check2"></i></button>
            <?php endif; ?>
            <button class="btn btn-sm btn-outline-danger btn-delete-msg" data-id="<?= $row['id'] ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>

        <!-- Message detail modal -->
        <div class="modal fade" id="msgModal<?= $row['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <h6 class="modal-title"><?= e($row['subject'] ?: 'Message') ?></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <p><strong>From:</strong> <?= e($row['name']) ?> (<?= e($row['email']) ?>)</p>
                <?php if ($row['phone']): ?><p><strong>Phone:</strong> <?= e($row['phone']) ?></p><?php endif; ?>
                <p><strong>Received:</strong> <?= format_date($row['created_at'], 'd M Y, h:i A') ?></p>
                <hr>
                <p style="white-space:pre-wrap;"><?= e($row['message']) ?></p>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="mt-3"><?= render_pagination($p['totalPages'], $p['currentPage'], 'messages.php?q=' . urlencode($search)) ?></div>

<script>
$(function(){
  $('.btn-mark-read').on('click', function(){
    const id = $(this).data('id'); const row = $('#msg-row-'+id);
    $.post('ajax/mark_message_read.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) { row.removeClass('fw-bold'); row.find('.btn-mark-read').remove(); }
    }, 'json');
  });
  $('.btn-delete-msg').on('click', function(){
    if (!confirm('Delete this message?')) return;
    const id = $(this).data('id');
    $.post('ajax/delete_message.php', {id:id, csrf_token:'<?= csrf_token() ?>'}, function(res){
      if (res.success) { $('#msg-row-'+id).fadeOut(200, function(){ $(this).remove(); }); }
      else { alert(res.message || 'Failed to delete.'); }
    }, 'json');
  });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
