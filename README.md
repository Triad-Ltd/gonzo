What does it do, anyway?

It sort of enables searching pdfs, docs, and text files with a normal expressionengine search tag.  I say sort of, since the contents are stored in a field, and that field is set to be searchable.

----------------------------

Why is it called gonzo?

Ask a bristolian.

----------------------------

Requirements:

linux (i cant see this working on windows).
pdftotext 
antiword
shell_exec() on php.

----------------------------

Instructions:

Once all requirements are met (install pdftotext and antiword), clone into ee_system_folder/expressionengine/third_party folder.

Install both extention and field type.

Add new 'gonzo' field to channel field group, enabling 'search' and disabling 'required'. Note its settings require you to set a file field from the same channel.

When you publish or edit an entry with a pdf, word (doc or docx) or txt file attachment, its contents will be streamed into the new 'gonzo' field. You can use this, in effect, to search your attachments.

Hope it helps.
