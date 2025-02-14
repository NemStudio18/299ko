<section>
    <header>{{ Lang.contact.content }}</header>
    <form method="post" action="{{ ROUTER.generate("contact-saveConfig") }}">
        {{ show.tokenField() }}
        <p>
            <label for="content1">{{ Lang.contact.before_form }}</label>
            <textarea class="editor" name="content1" id="content1">{% HOOK.beforeEditEditor( runPlugin.getConfigVal("content1")) %}</textarea><br>
            {{ filemanagerDisplayManagerButton() }}
        </p>
        <p>
            <label for="content2">{{ Lang.contact.after_form }}</label><br>
            <textarea class="editor" name="content2" id="content2">{% HOOK.beforeEditEditor( runPlugin.getConfigVal("content2")) %}</textarea><br>
            {{ filemanagerDisplayManagerButton() }}
        </p>
        <button type="submit" class="button">{{ Lang.save }}</button>
    </form>
</section>
<section>
    <header>{{ Lang.contact.collected_email_addresses }}</header>
    <p>
        <label for="savedMails">{{ Lang.contact.emails_collected }}</label>
        <textarea readonly="readonly" id="savedMails">{{ emails }}</textarea>
    </p>
    <a href="{{ ROUTER.generate("contact-empty-mails", ["token" => token]) }}" class="button alert"
       onclick="return(confirm('{{ Lang.contact.confirm_empty_mail_base }}'));">{{ Lang.contact.delete_base }}</a> 
</section>