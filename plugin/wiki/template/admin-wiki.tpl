<h1>{{ Lang.wiki.list_pages }}</h1>
<ul>
    {% for page in pages %}
        <li>
            <a href="{{ router.generate("admin-wiki-edit") }}?page={{ page }}">{{ page }}</a>
        </li>
    {% endfor %}
</ul>
<a href="{{ router.generate("admin-wiki-edit") }}?page=NouvellePage.md">{{ Lang.wiki.new_page }}</a>
