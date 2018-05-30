<?php
/**
 * @var array $lot
 * @var array $bets
 * @var array $categories
 */
$min_cost = $lot['current_price'] + $lot['bet_step'];
?>
<?= renderTemplate('templates/nav.php', ['categories' => $categories]) ?>
<section class="lot-item container">
    <h2><?= htmlspecialchars($lot['name']) ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?= $lot['image_url'] ?>" width="730" height="548" alt="<?= htmlspecialchars($lot['name']) ?>">
            </div>
            <p class="lot-item__category">Категория: <span><?= htmlspecialchars($lot['category_name']) ?></span></p>
            <p class="lot-item__description"><?= htmlspecialchars($lot['description']) ?></p>
        </div>
        <div class="lot-item__right">
            <?php if (isset($_SESSION['user'])): ?>
                <div class="lot-item__state">
                    <div class="lot-item__timer timer"><?= date_create()->diff(date_create('tomorrow'))->format('%h:%I') ?></div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= rurNumberFormat($lot['current_price']) ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= rurNumberFormat($min_cost) ?></span>
                        </div>
                    </div>
                    <form class="lot-item__form" action="https://echo.htmlacademy.ru" method="post">
                        <p class="lot-item__form-item">
                            <label for="cost">Ваша ставка</label>
                            <input id="cost" type="number" name="cost"
                                   placeholder="<?= number_format($min_cost, 0, '.', ' ') ?>">
                        </p>
                        <button type="submit" class="button">Сделать ставку</button>
                    </form>
                </div>
            <?php endif; ?>
            <div class="history">
                <h3>История ставок (<span><?= count($bets) ?></span>)</h3>
                <table class="history__list">
                    <?php foreach ($bets as $bet): ?>
                        <tr class="history__item">
                            <td class="history__name"><?= $bet['user_name'] ?></td>
                            <td class="history__price"><?= rurNumberFormat($bet['price']) ?></td>
                            <td class="history__time"><?= date('d.m.y в H:i', $bet['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>
</section>
