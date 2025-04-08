<!-- templates/admin/wiki-list.tpl -->
<h1>Liste des pages Wiki</h1>
<div class="admin-menu">
    {{ adminMenu }}
</div>
<table border="1" cellspacing="0" cellpadding="4">
    <thead>
        <tr>
            <th>Titre</th>
            <th>Catégorie</th>
            <th>Parent</th>
            <th>Date de Création</th>
            <th>Last update</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        {% for page in pagesTree %}
        <tr>
            <td>{{ page.title }}</td>
            <td>{{ page.categoryName }}</td>
            <td>{{ page.parentTitle }}</td>
            <td>{{ page.created_at }}</td>
            <td>{{ page.updated_at }}</td>
            <td>
                <a href="{{ router.generate("admin-wiki-edit") }}?page={{ page.filename }}">Éditer</a>
                <a href="{{ router.generate("admin-wiki-delete") }}?page={{ page.filename }}" onclick="return confirm('Supprimer cette page ?');">Supprimer</a>
            </td>
        </tr>
        {% if page.children %}
            {% for child in page.children %}
            <tr>
                <td>&nbsp;&nbsp;&nbsp;-- {{ child.title }}</td>
                <td>{{ child.categoryName }}</td>
                <td>{{ child.parentTitle }}</td>
                <td>{{ child.created_at }}</td>
                <td>{{ child.updated_at }}</td>
                <td>
                    <a href="{{ router.generate("admin-wiki-edit") }}?page={{ child.filename }}">Éditer</a>
                    <a href="{{ router.generate("admin-wiki-delete") }}?page={{ child.filename }}" onclick="return confirm('Supprimer cette page ?');">Supprimer</a>
                </td>
            </tr>
            {% endfor %}
        {% endif %}
        {% endfor %}
    </tbody>
</table>
<a href="{{ router.generate("admin-wiki-edit") }}">Nouvelle page</a>
