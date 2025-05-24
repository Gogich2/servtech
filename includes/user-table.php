<table class="users-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Ім'я</th>
            <th>Email</th>
            <th>Дата народження</th>
            <th>Дата реєстрації</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($user = $users->fetchArray(SQLITE3_ASSOC)): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= $user['birthdate'] ?></td>
            <td><?= $user['reg_date'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>