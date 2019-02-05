<style type="text/css" media="screen">
    #editor { 
        width: 95%;
        height: 500px;
        border: 1px solid #ccc;
    }
</style>
<div id="main">
    <h3>Business Profile Layout Editor (Advanced)</h3>
    <p>You can edit the layout of your site here. Be careful, this is primarily for developers.</p>
    <div id="editor"><?php echo htmlentities(Broadstreet_View::load('listings/single/default', array(), true, false)) ?></div>
</div>
<div class="clearfix"></div>
<script src="http://d1n0x3qji82z53.cloudfront.net/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>
<script>
    var editor = ace.edit("editor");
    editor.setTheme("ace/theme/dawn");
    editor.getSession().setMode("ace/mode/php");
</script>