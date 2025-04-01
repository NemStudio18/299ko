<h1>{{ Lang.wiki.edit_page }} : {{ page }}</h1>
<form method="post" action="{{ router.generate("admin-wiki-save") }}">
    <input type="hidden" name="page" value="{{ page }}">
    <textarea name="content" rows="20" cols="80">{{ content }}</textarea>
    <br>
    <button type="submit">{{ Lang.wiki.save }}</button>
</form>
