
<!-- template_view.php — это шаблон, содержащий общую для всех страниц разметку. -->
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="utf-8">
    <title>Главная</title>
    <link rel="stylesheet" type="text/css" href="/css/styles.css" />
    <script src="/js/main.js" type="text/javascript"></script>
</head>
<body>
    <?php include 'application/views/'.$content_view; ?>
</body>
</html>