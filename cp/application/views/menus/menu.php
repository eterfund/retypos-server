<!-- Шапка-меню -->
<nav class="header navbar navbar-expand-md navbar-light bg-light">
    <a href="#" class="navbar-brand">
        <img src="//time.office.etersoft.ru/images/logo.png" width="30" height="30" alt="Etersoft Logo">
        TYPOS@ETERSOFT - Сервис опечаток
    </a>

    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            area-controls="#navbarNav" area-expanded="false" area-label="Раскрыть меню">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse " id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <?foreach ($menuItems as $index => $item): ?>
                <?if ($index === 0): ?>
                    <li class="nav-item active">
                        <a href="<?= $item["href"] ?>" class="nav-link">
                            <?= $item["name"] ?>
                            <span class="sr-only">(текущая)</span>
                        </a>
                    </li>
                <?else:?>
                    <li class="nav-item">
                        <a href="<?= $item["href"] ?>" class="nav-link">
                            <?= $item["name"] ?>
                        </a>
                    </li>
                <?endif;?>
            <?endforeach;?>
        </ul>

        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="<?=$base_url?>index.php/authorization/logout">Выйти</a>
            </li>
        </ul>
    </div>
</nav>