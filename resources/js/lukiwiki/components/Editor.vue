<template>
    <div>
        <div class="btn-toolbar justify-content-between mb-1" role="toolbar" aria-label="Toolbar with button groups">
            <div class="input-group">
                <div class="input-group-prepend">
                    <label class="input-group-text" for="pagename-textbox">Page Name</label>
                </div>
                <input type="text" class="form-control" id="pagename-textbox" name="page" />
            </div>
            <div class="btn-group" role="group" aria-label="Basic Button">
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Bold" v-on:click="replace('b')">
                    <i class="fa fa-bold"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Italic" v-on:click="replace('i')">
                    <i class="fa fa-italic"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Strike" v-on:click="replace('s')">
                    <i class="fa fa-strikethrough"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Underline" v-on:click="replace('u')">
                    <i class="fa fa-underline"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Code" v-on:click="replace('code')">
                    <i class="fa fa-code"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Quotation" v-on:click="replace('q')">
                    <i class="fa fa-quote-left"></i>
                </button>
            </div>
            <div class="btn-group" role="group" aria-label="First group">
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Insert Link" v-on:click="replace('url')">
                    <i class="fa fa-link"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Font size" v-on:click="replace('size')">
                    <i class="fa fa-text-height"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Color" v-on:click="insert('color')">color</button>
            </div>
            <div class="btn-group" role="group" aria-label="Misc group">
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Convert character reference" v-on:click="replace('ncr')">&amp;#</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" title="Hint" v-on:click="hint()">
                    <i class="fa fa-question-circle"></i>
                </button>
            </div>
        </div>
        <div class="form-group">
            <codemirror v-model="editor" 
                      :options="cmOption"
                      ref="cm">
            </codemirror>
        </div>
    </div>
</template>

<script>
    import VueCodemirror from 'vue-codemirror'
    // language
    import '../codemirror_lukiwiki.js'
    // active-line.js
    import 'codemirror/addon/selection/active-line.js'
    // styleSelectedText
    import 'codemirror/addon/selection/mark-selection.js'
    import 'codemirror/addon/search/searchcursor.js'
    // highlightSelectionMatches
    import 'codemirror/addon/scroll/annotatescrollbar.js'
    import 'codemirror/addon/search/matchesonscrollbar.js'
    import 'codemirror/addon/search/searchcursor.js'
    import 'codemirror/addon/search/match-highlighter.js'
    // keyMap
    import 'codemirror/mode/clike/clike.js'
    import 'codemirror/addon/edit/matchbrackets.js'
    import 'codemirror/addon/comment/comment.js'
    import 'codemirror/addon/dialog/dialog.js'
    import 'codemirror/addon/dialog/dialog.css'
    import 'codemirror/addon/search/searchcursor.js'
    import 'codemirror/addon/search/search.js'
    import 'codemirror/keymap/sublime.js'
    // foldGutter
    //import 'codemirror/addon/fold/foldgutter.css'
    import 'codemirror/addon/fold/brace-fold.js'
    import 'codemirror/addon/fold/comment-fold.js'
    import 'codemirror/addon/fold/foldcode.js'
    import 'codemirror/addon/fold/foldgutter.js'
    import 'codemirror/addon/fold/indent-fold.js'
    import 'codemirror/addon/fold/markdown-fold.js'
    import 'codemirror/addon/fold/xml-fold.js'

    export default {
        data() {
            return {
                editor: document.querySelector('lw-editor textarea').value,
                cmOption: {
                    tabSize: 4,
                    foldGutter: true,
                    styleActiveLine: true,
                    lineNumbers: true,
                    line: true,
                    keyMap: "sublime",
                    mode: 'lukiwiki',
                    extraKeys: {
                        'F11'(cm) {
                            cm.setOption("fullScreen", !cm.getOption("fullScreen"))
                        },
                        'Esc'(cm) {
                            if (cm.getOption("fullScreen")) cm.setOption("fullScreen", false)
                        }
                    }
                }
            }
        },
        methods: {
            insert(v){
                let ret = '';
                switch (v){
                    case 'help' :
                        //$('#hint').dialog('open');
                    break;
                    case 'br':
                        ret = '&br;'+"\n";
                    break;
                    case 'color' :
                        //$('#color_palette').dialog('open');
                    break;
                    default:
                        ret = '&('+v+');';
                    break;
                }
                console.log(ret);
                const doc = editor.getDoc();
                const cursor = doc.getCursor();
                this.$refs.cm.codemirror.replaceRange(ret, cursor);
            },
            replace(v){
                let ret = '';
                let str = this.$refs.cm.codemirror.getSelection();
                switch (v){
                    case 'size' :
                        var val = prompt("font-size (rem)", '1');
                        if (!val || !val.match(/\d+/)){
                            return;
                        }
                        ret = '&size(' + val + '){' + str + '};';
                    break;
                    case 'ncr':
                        var i, len;
                        for(i = 0, len = str.length; i < len ; i++ ){
                            ret += ("&#"+(str.charCodeAt(i))+";");
                        }
                    break;
                    case 'b':
                        ret = "''" + str + "''";
                    break;
                    case 'i':
                        ret = "'''" + str + "'''";
                    break;
                    case 'u':
                        ret = '__' + str + '__';
                    break;
                    case 's':
                        ret = '%%' + str + '%%';
                    break;
                    case 'code' :
                        ret = '`' + str + '`';
                    break;
                    case 'q' :
                        ret = '@@@' + str + '@@@';
                    break;

                    case 'url':
                        //	var regex = "^s?https?://[-_.!~*'()a-zA-Z0-9;/?:@&=+$,%#]+$";
                        var my_link = prompt( 'URL:', 'https://');
                        if (my_link !== null) {
                            ret = '[[' + str + '>' + my_link + ']]';
                        }else{
                            return;
                        }
                    break;
                    default:
                        alert("error");
                        return;
                        break;
                }
                this.$refs.cm.codemirror.replaceSelection(ret);
            }
        }
    }
</script>

<style lang="scss">
</style>