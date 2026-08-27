<div class="cart-container">
    <div class="cart-header-row">
        <h2>🛒 <?= __('cart_title') ?></h2>

        <?php if (!empty($groupedItems)): ?>
            <form action="index.php?route=cart" method="POST" class="cart-actions-form">
                <button type="submit" name="action" value="clear" class="btn-clear-cart">
                    🗑️ <?= __('cart_btn_clear') ?>
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($groupedItems)): ?>
        <div class="empty-cart-box">
            <p><?= __('cart_empty') ?></p>
            <a href="index.php?route=catalog" class="btn-go-shopping"><?= __('cart_go_to_recipes') ?></a>
        </div>
    <?php else: ?>
        <div class="cart-content">
            <ul class="cart-items-list">
                <?php foreach ($groupedItems as $item): ?>
                    <li class="cart-item">
                        <div class="cart-item-main-row">
                            <div class="cart-item-info">
                                <span class="cart-item-check">✔</span>
                                <span class="cart-item-name"><?= htmlspecialchars($item['name']) ?></span>
                            </div>
                            <span class="cart-item-qty"><?= $item['total_qty'] ?> <?= htmlspecialchars($item['unit']) ?></span>
                        </div>

                        <?php if (!empty($item['breakdown'])): ?>
                            <div class="cart-item-breakdown">
                                <?php foreach ($item['breakdown'] as $b): ?>
                                    <div class="cart-breakdown-entry">
                                        <span class="cart-item-recipe">📖 <?= htmlspecialchars($b['recipe']) ?></span>
                                        <span class="cart-breakdown-qty"><?= $b['qty'] ?> <?= htmlspecialchars($item['unit']) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
</div>