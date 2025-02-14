<section>
    <header>{{ Lang.pluginsmanager.plugins-list }}</header>
    <form method="post" action="{{ ROUTER.generate("pluginsmanager-save") }}" id="pluginsmanagerForm">
        {{ show::tokenField }}
        <table>
            <thead>
                <tr>
                    <th>{{ Lang.pluginsmanager.plugin-name }}</th>
                    <th>{{ Lang.pluginsmanager.priority }}</th>
                    <th>{{ Lang.pluginsmanager.activate }}</th>
                </tr>
            </thead>
            <tbody>              
                {% for plugin in pluginsManager.getPlugins() %}
                    <tr>
                        <td>
                            {{ plugin.getTranslatedName() }}
                            {% if plugin.getInfoVal("version") != "none" %} (version {{ plugin.getInfoVal("version") }}){% endif %} : {{ plugin.getTranslatedDesc() }}
                            {% if plugin.getConfigVal("activate") && plugin.isInstalled() == false %}<p><a class="button" href="{{ ROUTER.generate("pluginsmanager-maintenance", ["plugin" => plugin.getName(), "token" => token]) }}">{{ Lang.pluginsmanager.maintenance-required }}</a></p>{% endif %}
                        </td>
                        <td>
                            <select name="priority[{{ plugin.getName() }}]" onchange="document.getElementById('pluginsmanagerForm').submit();">
                                {% for k, v in priority %}
                                    <option {% if plugin.getconfigVal("priority") == v %}selected{% endif %} value="{{ v }}">{{ v }}</option>
                                {% endfor %}
                            </select>
                        </td>
                        <td>
                            {% if plugin.isRequired() == false %}
                                <input onchange="document.getElementById('pluginsmanagerForm').submit();" id="activate[{{ plugin.getName() }}]" type="checkbox" name="activate[{{ plugin.getName() }}]" {% if plugin.getConfigVal("activate") %}checked{% endif %} />
                            {% else %}
                                <input style="display:none;" id="activate[{{ plugin.getName() }}]" type="checkbox" name="activate[{{ plugin.getName() }}]" checked />
                            {% endif %}
                        </td>
                    </tr>
                {% endfor %}
            </tbody>                    
        </table>
    </form>
</section>
