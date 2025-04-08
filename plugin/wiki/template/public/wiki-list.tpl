<!-- templates/public/wiki-list.tpl -->
<style>
.wiki {
    display: flex;
    flex-direction: row-reverse;
    flex-wrap: wrap;
    align-content: stretch;
    justify-content: space-between;
    align-items: flex-start;
    padding: 0 20px;
}

.flex-content {
    display: flex;
    flex-direction: row;
}
.wiki-content {
    flex: 1;
    margin-right: 20px;
}
.wiki-navigation {
    width: 250px;
}
</style>


<aside class="wiki-navigation">
<form method="get" action="{{ router.generate("wiki-view") }}">
    <input type="text" name="q" placeholder="Rechercher…" value="{{ searchQuery }}">
    <button type="submit">Rechercher</button>
</form>
        <h3>Navigation</h3>
        {{ menu }}

</aside>
<div class="flex-content">
<div class="wiki-content">
{% if pages %}
    <ul>
        {% for page in pages %}
            <li>
                <a href="{{ router.generate("wiki-view") }}?page={{ page.filename }}">{{ page.title }}</a>
            </li>
        {% endfor %}
    </ul>
{% else %}
    <p>Aucune page trouvée.</p>
{% endif %}</div></div>

