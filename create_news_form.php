


<form method="POST"  enctype="multipart/form-data" action="index.php?page=create_news">
    <div class="form-group">
        <label for="title">Заголовок</label>
        <input type="text" class="form-control" id="title" name="title" required>
        <div class="form-group"> <!-- Добавлен div для группировки label и input -->
        <label for="price">Цена</label>
        <input type="text" class="form-control" id="price" name="price" required> <!-- Исправлены id и name -->
    </div>
    </div>
    <label for="image" >Изображение:</label><br>
        <input type="file" id="image" name="image"><br><br>
    <div class="form-group">
        <label for="content">Содержание</label>
        <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
    </div>

    <button type="submit" name="submit" class="btn btn-primary">Опубликовать</button>
</form>