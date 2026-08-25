<?php if (count($students) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Gender</th>
                <th>Faculty</th>
                <th>Phone</th>
                <th>Email</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($students as $student): ?>
                <tr>
                    <td>
                        <div class="student-name"><?php echo e($student['name'] ?? '-'); ?></div>
                        <?php if (!empty($student['username'])): ?>
                            <div class="username"><?php echo e($student['username']); ?></div>
                        <?php endif; ?>
                    </td>
                    <td><?php echo e($student['gender'] ?? '-'); ?></td>
                    <td><span class="faculty"><?php echo e($student['faculty'] ?? '-'); ?></span></td>
                    <td><?php echo e($student['phone'] ?? '-'); ?></td>
                    <td><?php echo e($student['email'] ?? '-'); ?></td>
                    <td>
                        <div class="actions-inline">
                            <a href="view_student.php?id=<?php echo (int) $student['id']; ?>" class="action-btn">View</a>
                            <a href="edit_student.php?id=<?php echo (int) $student['id']; ?>" class="action-btn">Edit</a>
                            <?php if (!empty($student['username'])): ?>
                                <a
                                    href="reset_password.php?id=<?php echo (int) $student['id']; ?>"
                                    class="action-btn reset-btn"
                                    onclick="return confirm('Reset password for <?php echo e($student['name']); ?>?');"
                                >Reset Password</a>
                            <?php else: ?>
                                <span class="action-btn disabled">No Account</span>
                            <?php endif; ?>
                            <form method="POST" action="delete_student.php" onsubmit="return confirm('Delete <?php echo e($student['name']); ?>?');">
                                <input type="hidden" name="id" value="<?php echo (int) $student['id']; ?>">
                                <button type="submit" class="action-btn delete-btn">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <div class="no-students">
        <?php if ($search !== ''): ?>
            No students found for "<strong><?php echo e($search); ?></strong>".
        <?php else: ?>
            No students found.
        <?php endif; ?>
    </div>
<?php endif; ?>
