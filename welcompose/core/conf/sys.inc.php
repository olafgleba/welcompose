; <?php /*
[path]
wcom_admin_root_www = /admin
wcom_public_root_www = 

[log]
handler = "file"
name = "/www/gits/welcompose/welcompose/tmp/log/welcompose.log"
level = PEAR_LOG_WARNING

[environment]
debug = true
app_key = "!@R;&x.{Su}d&VcU#j5I\:)mU.2?!_."

[locales]
all = "de_DE.UTF-8"
numeric = "C"

[database]
driver = "pdo"
dsn = "mysql:unix_socket=/opt/local/var/run/mysql5/mysqld.sock;dbname=wcom-current"
username = "root"
password = 
table_alias_constants = true
debug = false
backticks = true

[media]
store_www = "/files/media"
store_disk = "/www/gits/welcompose/welcompose/files/media"
chmod = 

[global_file]
store_www = "/files/global_files"
store_disk = "/www/gits/welcompose/welcompose/files/global_files"
chmod = 

[caching]
index.php_mode = 0
index.php_lifetime = 0

[output]
gunzip = 0

[cookie]
lifetime = 2592000

[plugins]
textconverter_dir = "/www/gits/welcompose/welcompose/core/plugins/textconverters"
textmacro_dir = "/www/gits/welcompose/welcompose/core/plugins/textmacros"

[urls]
blog_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
blog_index_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>"
blog_index_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
blog_index_tag_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>&amp;start=<start>"
blog_index_start = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
blog_index_start_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>"
blog_index_start_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
blog_index_start_tag_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>&amp;start=<start>"
blog_atom_10 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Atom10"
blog_atom_10_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Atom10&amp;tag=<tag_word>"
blog_atom_10_start = "/index.php?project_name=<project_name>&amp;action=Atom10"
blog_atom_10_start_tag = "/index.php?project_name=<project_name>&amp;action=Atom10&amp;tag=<tag_word>"
blog_rss_20 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Rss20"
blog_rss_20_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Rss20&amp;tag=<tag_word>"
blog_rss_20_start = "/index.php?project_name=<project_name>&amp;action=Rss20"
blog_rss_20_start_tag = "/index.php?project_name=<project_name>&amp;action=Rss20&amp;tag=<tag_word>"
blog_comments_atom_10 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=CommentsAtom10"
blog_comments_atom_10_start = "/index.php?project_name=<project_name>&amp;action=CommentsAtom10"
blog_comments_rss_20 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=CommentsRss20"
blog_comments_rss_20_start = "/index.php?project_name=<project_name>&amp;action=CommentsRss20"
blog_item = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Item&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>&amp;posting_day_added=<posting_day_added>&amp;posting_title=<posting_title>"
blog_item_start = "/index.php?project_name=<project_name>&amp;action=Item&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>&amp;posting_day_added=<posting_day_added>&amp;posting_title=<posting_title>"
blog_archive_year = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=ArchiveYear&amp;posting_year_added=<posting_year_added>"
blog_archive_year_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=ArchiveYear&amp;posting_year_added=<posting_year_added>&amp;start=<start>"
blog_archive_year_start = "/index.php?project_name=<project_name>&amp;action=ArchiveYear&amp;posting_year_added=<posting_year_added>"
blog_archive_year_start_pager = "/index.php?project_name=<project_name>&amp;action=ArchiveYear&amp;posting_year_added=<posting_year_added>&amp;start=<start>"
blog_archive_month = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=ArchiveMonth&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>"
blog_archive_month_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=ArchiveMonth&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>&amp;start=<start>"
blog_archive_month_start = "/index.php?project_name=<project_name>&amp;action=ArchiveMonth&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>"
blog_archive_month_start_pager = "/index.php?project_name=<project_name>&amp;action=ArchiveMonth&amp;posting_year_added=<posting_year_added>&amp;posting_month_added=<posting_month_added>&amp;start=<start>"
event_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
event_index_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>"
event_index_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
event_index_tag_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>&amp;start=<start>"
event_index_start = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
event_index_start_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>"
event_index_start_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
event_index_start_tag_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;tag=<tag_word>&amp;start=<start>"
event_atom_10 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Atom10"
event_atom_10_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Atom10&amp;tag=<tag_word>"
event_atom_10_start = "/index.php?project_name=<project_name>&amp;action=Atom10"
event_atom_10_start_tag = "/index.php?project_name=<project_name>&amp;action=Atom10&amp;tag=<tag_word>"
event_rss_20 = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Rss20"
event_rss_20_tag = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Rss20&amp;tag=<tag_word>"
event_rss_20_start = "/index.php?project_name=<project_name>&amp;action=Rss20"
event_rss_20_start_tag = "/index.php?project_name=<project_name>&amp;action=Rss20&amp;tag=<tag_word>"
generator_form_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
generator_form_index_start = "/index.php?project_name=<project_name>&amp;action=Index"
global_template_url = "/global_template.php?project_name=<project_name>&amp;name=<global_template_name>"
global_template_url_start = "/global_template.php?project_name=<project_name>&amp;name=<global_template_name>&amp;&amp;start=<start>"
simple_form_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
simple_form_index_start = "/index.php?project_name=<project_name>&amp;action=Index"
simple_guestbook_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
simple_guestbook_index_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
simple_guestbook_index_start = "/index.php?project_name=<project_name>&amp;action=Index"
simple_guestbook_index_start_pager = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index&amp;start=<start>"
simple_page_index = "/index.php?project_name=<project_name>&amp;page_name=<page_name>&amp;action=Index"
simple_page_index_start = "/index.php?project_name=<project_name>&amp;action=Index"

[flickr]
cache_dir = "/www/gits/welcompose/welcompose/tmp/flickr_cache"
cache_encrypt = true
api_key = "11bcda9f77519a4f44121ce5ee5b6a8f"

; */ ?>
