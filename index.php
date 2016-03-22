<?php require_once('main.php') ?>
<!doctype html>
<html>
    <head>
        <title>Who's Off The Hook Today?</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
        <h1>Who's Off The Hook Today?</h1>
        <ul>
        <?php foreach ($dates as $date) : ?>
            <li class="<?php echo htmlentities($date['type']) ?> <?php echo isOnWeekend($date['date']) ? 'weekend' : 'week' ?>">
                <p class="date"><?php echo htmlentities($date['date']->format('l jS \of F')) ?></p>
                <p class="message"><?php echo htmlentities($date['message']) ?></p>
            </li>
        <?php endforeach ?>
        </ul>
    </body>
</html>
