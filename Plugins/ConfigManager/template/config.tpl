<form id="configForm" method="post" action="{{link}}" autocomplete="off">
    {{SHOW.tokenField}}
    <section>
        <header>{{Lang.configmanager-settings}}</header>
        <p>
            <input {% if CORE.getConfigVal("hideTitles") %}checked{% endif %} type="checkbox" name="hideTitles" id="hideTitles" /> <label for="hideTitles">{{Lang.configmanager-hide-titles}}</label>
        </p>
        <p>
            <label for="defaultPlugin">{{Lang.configmanager-public-default-plugin}}</label>
            <select name="defaultPlugin">
                {% for plugin in pluginsManager.getPlugins %}
                    {% if plugin.getCOnfigVal("activate") && plugin.getIsCallableOnPublic %}
                        <option {% if plugin.getIsDefaultPlugin %}selected{% endif %} value="{{plugin.getName}}">{{plugin.getInfoVal("name")}}</option>
                    {% endif %}
                {% endfor %}
            </select>
        </p>
        <p>
            <label for="defaultAdminPlugin">{{Lang.configmanager-admin-default-plugin}}</label>
            <select name="defaultAdminPlugin">
                {% for plugin in pluginsManager.getPlugins %}
                    {% if plugin.getCOnfigVal("activate") && plugin.getIsCallableOnAdmin %}
                        <option {% if plugin.getIsDefaultAdminPlugin %}selected{% endif %} value="{{plugin.getName}}">{{plugin.getInfoVal("name")}}</option>
                    {% endif %}
                {% endfor %}
            </select>
        </p>
        <p>
            <label for="siteName">{{Lang.configmanager-sitename}}</label>
            <input type="text" name="siteName" id="siteName" value="{{ CORE.getConfigVal("siteName")}}" required />
        </p>
        <p>
            <label for="siteDesc">{{Lang.configmanager-sitedesc}}</label>
            <input type="text" name="siteDesc" value="{{ CORE.getConfigVal("siteDesc")}}"/>
        </p>
        <p>
            <label for="siteLang">{{Lang.configmanager-sitelang}}</label>
            <select name="siteLang">
                {% for k, v in lang.getAvailablesLocales %}
                    <option {% if lang.getLocale == k %}selected{% endif %} value="{{k}}">{{v}}</option>
                {% endfor %}
            </select>
        </p>
        <p>
            <label for="theme">{{Lang.configmanager-theme}}</label>
            <select name="theme">
                {% for k, v in CORE.getThemes %}
                    <option {% if CORE.getConfigVal("theme") == k %}selected{% endif %} value="{{k}}">{{v.name}}</option>
                {% endfor %}
            </select>
        </p>
    </section>
    <section>
        <header>{{Lang.configmanager-advanced-settings}}</header>
        <p>
            <label id='delCacheDesc'>{{Lang.configmanager-delete-cache-desc}}</label><br>
            <a aria-describedby="delCacheDesc" class="button" href="{{cacheClearLink}}">{{Lang.configmanager-delete-cache}}</a>
        </p>
        <p>
            <input {% if CORE.getConfigVal("debug") %}checked{% endif %} type="checkbox" name="debug" /> <label for="debug">{{Lang.configmanager-debug}}</label> 
        </p>
        <p>
            <label>{{Lang.configmanager-siteurl}}</label><br>
            <input type="text" name="siteUrl" value="{{CORE.getConfigVal("siteUrl")}}" />
        </p>
        <p>
            <label for="htaccess">{{Lang.configmanager-htaccess}}</label><br>
            <textarea id="htaccess" name="htaccess">{{CORE.getHtaccess}}</textarea>
        </p>
    </section>
    <p>

        <button type="submit" class="button success">{{Lang.submit}}</button></p>
</form>
