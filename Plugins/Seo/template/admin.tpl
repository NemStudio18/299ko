<form method="post" action="{{ ROUTER.generate("seo-admin-save")}}">
    {{ show.tokenField() }}
    <section>
        <header>{{ Lang.seo.display }}</header>
        <p>
            <label for="position">{{ Lang.seo.menu-position }}</label><br>
            <select name="position" id="position">
                <option value="menu" {% if position == "menu" %}selected{% endif %}>{{ Lang.seo.nav-menu }}</option>
                <option value="footer" {% if position == "footer" %}selected{% endif %}>{{ Lang.seo.top-footer-page }}</option>
                <option value="endfooter" {% if position == "endfooter" %}selected{% endif %}>{{ Lang.seo.bottom-footer-page }}</option>
                <option value="float" {% if position == "float" %}selected{% endif %}>{{ Lang.seo.float }}</option>
            </select>
        </p>
    </section>
    <section>
        <header>{{ Lang.seo.google }}</header>
        <p>
            <label for="trackingId">{{ Lang.seo.analytics.id }}</label><br>
            <input type="text" name="trackingId" id="trackingId" value="{{ runPlugin.getConfigVal("trackingId") }}" />
        </p>
        <p>
            <label for="wt">{{ Lang.seo.analytics.meta }}</label><br>
            <input type="text" name="wt" id="wt" value="{{ runPlugin.getConfigVal("wt") }}" />
        </p>
    </section>
    <section>
        <header>{{ Lang.seo.socials-links }}</header>
        {% set social = seoGetSocialVars() %}
        {% for k, v in social %}
        <p>
            <label for="{{ v }}"><i class="fa-brands fa-{{ v }}"></i>&nbsp;{{ k }}</label><br>
            <input placeholder="" type="text" name="{{ v }}" id="{{ v }}" value="{{ runPlugin.getConfigVal(v) }}" />
        </p>
        {% endfor %}
        <p>
            <button type="submit" class="button success">{{ Lang.submit }}</button>
        </p>
    </section>
</form>
