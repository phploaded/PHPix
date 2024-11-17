<?php 
admin_only();
// Handle delete request
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $qry = "DELETE FROM `px_spots` WHERE `id` = $id";
    if (mysqli_query($con, $qry)) {
        echo "<script>alert('Row deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error deleting row: " . mysqli_error($con) . "');</script>";
    }
}

// Handle edit request
if (isset($_POST['edit'])) {
    $id = intval($_POST['id']);
    $newTitle = mysqli_real_escape_string($con, $_POST['title']);
    $oldTitle = mysqli_real_escape_string($con, $_POST['old_title']);
    $sort = intval($_POST['sort']);
    $updateAll = isset($_POST['update_all']) && $_POST['update_all'] == '1';

    // Update px_spots table
    $qry = "UPDATE `".$prefix."spots` SET `title` = '$newTitle', `sort` = $sort WHERE `id` = $id";
    if (mysqli_query($con, $qry)) {
        if ($updateAll) {
            // Update px_uploads table
            $updateQry = "UPDATE `".$prefix."uploads` 
                          SET `spots` = REPLACE(`spots`, '$oldTitle', '$newTitle') 
                          WHERE `spots` LIKE '%$oldTitle%'";
            if (mysqli_query($con, $updateQry)) {
                echo "<script>alert('Row and associated spots updated successfully!');</script>";
            } else {
                echo "<script>alert('Error updating associated spots: " . mysqli_error($con) . "');</script>";
            }
        } else {
            echo "<script>alert('Row updated successfully!');</script>";
        }
    } else {
        echo "<script>alert('Error updating row: " . mysqli_error($con) . "');</script>";
    }
}
?>
<div class="page-header"><h2><i class="fa fa-tags"></i> Manage Spots</h2></div>
        <table id="spots-table" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>UID</th>
                    <th>Sort</th>
                    <th>Title</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $qry = "SELECT * FROM `".$prefix."spots`";
                $result = mysqli_query($con, $qry);
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['uid']}</td>
                        <td>{$row['sort']}</td>
                        <td>{$row['title']}</td>
                        <td>
                            <a href='javascript:void(0)' class='btn btn-warning btn-sm' onclick='editRow({$row['id']}, \"{$row['title']}\", {$row['sort']})'>Edit</a>
                            <a href='phpix-manage.php?page=spots&delete={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this row?\")'>Delete</a>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

    <!-- Edit Modal -->
    <div class="modal fade" id="edit-modal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title" id="editModalLabel">Edit Spot</h4>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit-id">
                        <input type="hidden" name="old_title" id="edit-old-title">
                        <div class="form-group">
                            <label for="edit-title">Title:</label>
                            <input type="text" class="form-control" name="title" id="edit-title" required>
                        </div>
                        <div class="form-group">
                            <label for="edit-sort">Sort:</label>
                            <input type="number" class="form-control" name="sort" id="edit-sort" required>
                        </div>
                        <div class="form-group">
                            <label>Update Options:</label><br>
                            <label class="radio-inline">
                                <input type="radio" name="update_all" value="1" checked>
                                Update all existing spots with the same name
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="update_all" value="0">
                                Edit only the template
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" name="edit" class="btn btn-primary">Save Changes</button>
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#spots-table').DataTable({
                responsive: true
            });
        });

        function editRow(id, title, sort) {
            $('#edit-id').val(id);
            $('#edit-title').val(title);
            $('#edit-sort').val(sort);
            $('#edit-old-title').val(title);
            $('#edit-modal').modal('show');
        }
    </script>
