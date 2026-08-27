<section id="tab-users" class="admin-tab-content active">
    <form method="GET" class="table-controls">
        <input type="hidden" name="route" value="admin">
        <input type="hidden" name="tab" value="users">
        <input type="text" name="user_q" value="<?= htmlspecialchars($userSearch) ?>" placeholder="<?= __('admin_search_user') ?>" class="search-input">
        <div class="filter-group">
            <select name="user_sort" class="select-input sort-select" onchange="this.form.submit()">
                <option value="created_desc" <?= $userSort === 'created_desc' ? 'selected' : '' ?>><?= __('admin_sort_reg_desc') ?></option>
                <option value="created_asc" <?= $userSort === 'created_asc' ? 'selected' : '' ?>><?= __('admin_sort_reg_asc') ?></option>
                <option value="name_asc" <?= $userSort === 'name_asc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_asc') ?></option>
                <option value="name_desc" <?= $userSort === 'name_desc' ? 'selected' : '' ?>><?= __('admin_sort_alpha_desc') ?></option>
            </select>
        </div>
        <button type="submit" class="btn btn-secondary admin-btn-add-sm"><?= __('admin_search_button') ?></button>
    </form>

    <table class="admin-table table-users">
        <thead>
            <tr>
                <th class="col-u-id"><?= __('admin_th_id') ?></th>
                <th class="col-u-name"><?= __('admin_th_username') ?></th>
                <th class="col-u-date"><?= __('admin_th_reg_date') ?></th>
                <th class="col-u-status"><?= __('admin_th_status') ?></th>
                <th class="col-u-warn"><?= __('admin_th_warnings') ?></th>
                <th class="col-u-act"><?= __('admin_th_actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td class="col-u-id"><?= (int)$user['id'] ?></td>
                    <td class="col-u-name"><strong>@<?= htmlspecialchars($user['username']) ?></strong><br><small><?= htmlspecialchars($user['display_name']) ?></small></td>
                    <td class="col-u-date"><?= htmlspecialchars(date('d.m.Y', strtotime($user['created_at']))) ?></td>
                    <td class="col-u-status"><span class="<?= $user['status'] === 'banned' ? 'status-banned' : 'status-active' ?>"><?= $user['status'] === 'banned' ? __('admin_status_banned') : __('admin_status_active') ?></span></td>
                    <td class="col-u-warn"><span class="badge <?= (int)$user['warnings'] > 0 ? 'badge-warning' : 'badge-muted' ?>"><?= (int)$user['warnings'] ?> <?= __('admin_warning_suffix') ?></span></td>
                    <td class="col-u-act">
                        <a href="index.php?route=profile&id=<?= (int)$user['id'] ?>" class="link-profile-btn"><?= __('admin_btn_view_profile') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>