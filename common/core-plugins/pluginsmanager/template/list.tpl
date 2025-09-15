<section>
    <header>{{ Lang.pluginsmanager.plugins-list }}</header>
    <form method="post" action="{{ ROUTER.generate("pluginsmanager-save") }}" id="pluginsmanagerForm">
        {{ show::tokenField }}
        <table>
            <thead>
                <tr>
                    <th>{{ Lang.pluginsmanager.plugin-name }}</th>
                    <th>{{ Lang.pluginsmanager.plugin-version }}</th>
                    <th>{{ Lang.pluginsmanager.priority }}</th>
                    <th>{{ Lang.pluginsmanager.activate }}</th>
                </tr>
            </thead>
            <tbody>
                {% for plugin in plugins %}
                    <tr>
                        <td>
                            {{ plugin.getTranslatedName() }}
                             : {{ plugin.getTranslatedDesc() }}
                            {% if plugin.getConfigVal("activate") && plugin.isInstalled() == false %}
                                <p>
                                    <a class="button" href="{{ ROUTER.generate("pluginsmanager-maintenance", ["plugin" => plugin.getName(), "token" => token]) }}">{{ Lang.pluginsmanager.maintenance-required }}</a>
                                </p>
                            {% endif %}
                        </td>
                        <td>{{ plugin.getInfoVal("version") }}</td>
                        <td>
                            <select name="priority[{{ plugin.getName() }}]" onchange="document.getElementById('pluginsmanagerForm').submit();">
                                {% for k, v in priority %}
                                    <option {% if plugin.getconfigVal("priority") == v %}selected{% endif %} value="{{ v }}">{{ v }}</option>
                                {% endfor %}
                            </select>
                        </td>
                        <td>
                            <input onchange="document.getElementById('pluginsmanagerForm').submit();" id="activate[{{ plugin.getName() }}]" type="checkbox" name="activate[{{ plugin.getName() }}]" {% if plugin.getConfigVal("activate") %}checked{% endif %} />
                        </td>
                    </tr>
                {% endfor %}
            </tbody>
        </table>
    </form>
</section>
