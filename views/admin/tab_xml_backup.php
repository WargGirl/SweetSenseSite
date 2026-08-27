<section id="tab-xml_backup" class="admin-tab-content active">
    <div class="table-controls">
        <form method="POST">
            <input type="hidden" name="xml_action" value="export">
            <button type="submit" class="btn btn-primary"><?= __('admin_xml_btn_export') ?></button>
        </form>
    </div>

    <?= $xmlTableHtml; ?>
</section>