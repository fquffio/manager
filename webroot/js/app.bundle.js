(window.webpackJsonp=window.webpackJsonp||[]).push([[2],{116:function(t,e){},132:function(t,e,n){t.exports=n(75)},75:function(t,e,n){"use strict";n.r(e);n(70),n(47),n(63),n(41),n(40),n(62),n(61),n(122),n(121),n(120);var i=n(11),a=n.n(i),o=(n(116),n(20),{props:{ids:{type:String,default:function(){return[]}}},data:function(){return{allIds:[],selectedRows:[],status:""}},created:function(){try{this.allIds=JSON.parse(this.ids)}catch(t){console.error(t)}},computed:{selectedIds:function(){return JSON.stringify(this.selectedRows)},allChecked:function(){return JSON.stringify(this.selectedRows.sort())==JSON.stringify(this.allIds.sort())}},methods:{toggleAll:function(){this.allChecked?this.unCheckAll():this.checkAll()},checkAll:function(){this.selectedRows=JSON.parse(JSON.stringify(this.allIds))},unCheckAll:function(){this.selectedRows=[]},exportSelected:function(){this.selectedRows.length<1||document.getElementById("form-export").submit()},setStatus:function(t,e){this.selectedRows.length<1||(this.status=t,this.$nextTick(function(){document.getElementById("form-status").submit()}))},trash:function(){this.selectedRows.length<1||confirm("Move "+this.selectedRows.length+" item to trash")&&document.getElementById("form-delete").submit()},selectRow:function(t){if("checkbox"!=t.target.type){t.preventDefault();var e=t.target.querySelector("input[type=checkbox]"),n=this.selectedRows.indexOf(e.value);-1!=n?this.selectedRows.splice(n,1):this.selectedRows.push(e.value)}}}}),r=(n(19),n(18),n(17),n(53),"staggered"),s={template:'\n        <transition-group appear\n            name="'.concat(r,'"\n            v-on:enter="enter"\n            v-on:after-enter="afterEnter">\n            <slot></slot>\n        </transition-group>'),props:{stagger:{type:String,default:function(){return 50}}},methods:{enter:function(t,e){var n=this;t.classList.remove("".concat(r,"-enter-to")),t.classList.add("".concat(r,"-enter"));var i=this.getDelay(t);setTimeout(function(){n.$nextTick(function(){t.classList.add("".concat(r,"-enter")),t.classList.remove("".concat(r,"-enter-to")),t.classList.remove("".concat(r,"-enter-active"))}),e()},i)},afterEnter:function(t){this.$nextTick(function(){t.classList.remove("".concat(r,"-enter")),t.classList.remove("".concat(r,"-enter-to"))})},getDelay:function(t){return t.dataset&&t.dataset.index*this.stagger+5}}};n(101),n(99),n(97),n(95),n(94),n(93),n(89);function c(t){return function(t){if(Array.isArray(t)){for(var e=0,n=new Array(t.length);e<t.length;e++)n[e]=t[e];return n}}(t)||function(t){if(Symbol.iterator in Object(t)||"[object Arguments]"===Object.prototype.toString.call(t))return Array.from(t)}(t)||function(){throw new TypeError("Invalid attempt to spread non-iterable instance")}()}var l,d,u={count:1,page:1,page_size:20,page_count:1},h={data:function(){return{objects:[],endpoint:null,pagination:u}},methods:{getPaginatedObjects:function(){var t=this,e=!(arguments.length>0&&void 0!==arguments[0])||arguments[0],n=window.location.href;if(this.endpoint){var i="".concat(n,"/").concat(this.endpoint);return i=this.setPagination(i),fetch(i,{credentials:"same-origin",headers:{accept:"application/json"}}).then(function(t){return t.json()}).then(function(n){var i=(Array.isArray(n.data)?n.data:[n.data])||[];return n.data||(i=[]),e&&(t.objects=i),t.pagination=n.meta&&n.meta.pagination||t.pagination,i}).catch(function(t){console.error(t)})}return Promise.reject()},setPagination:function(t){var e=this,n="",i="?";return Object.keys(this.pagination).forEach(function(t,i){n+="".concat(i?"&":"").concat(t,"=").concat(e.pagination[t])}),-1===t.indexOf(i)||(i="&"),"".concat(t).concat(i).concat(n)},findObjectById:function(t){var e=this.objects.filter(function(e){return e.id===t});return e.length&&e[0]},loadMore:(l=regeneratorRuntime.mark(function t(){var e,n,i,a,o=arguments;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(e=o.length>0&&void 0!==o[0]?o[0]:u.page_size,!(this.pagination.page_items<this.pagination.count)){t.next=8;break}return t.next=4,this.nextPage(!1);case 4:i=t.sent,this.pagination.page_items=this.pagination.page_items+e<=this.pagination.count?this.pagination.page_items+e:this.pagination.count,a=this.objects.length,(n=this.objects).splice.apply(n,[a,0].concat(c(i)));case 8:case"end":return t.stop()}},t,this)}),d=function(){var t=this,e=arguments;return new Promise(function(n,i){var a=l.apply(t,e);function o(t,e){try{var o=a[t](e),c=o.value}catch(t){return void i(t)}o.done?n(c):Promise.resolve(c).then(r,s)}function r(t){o("next",t)}function s(t){o("throw",t)}r()})},function(){return d.apply(this,arguments)}),toPage:function(t){return this.pagination.page=t||1,this.getPaginatedObjects(!0)},firstPage:function(){var t=!(arguments.length>0&&void 0!==arguments[0])||arguments[0];return 1!==this.pagination.page?(this.pagination.page=1,this.getPaginatedObjects(t)):Promise.resolve([])},lastPage:function(){var t=!(arguments.length>0&&void 0!==arguments[0])||arguments[0];return this.pagination.page!==this.pagination.page_count?(this.pagination.page=this.pagination.page_count,this.getPaginatedObjects(t)):Promise.resolve([])},nextPage:function(){var t=!(arguments.length>0&&void 0!==arguments[0])||arguments[0];return this.pagination.page<this.pagination.page_count?(this.pagination.page=this.pagination.page+1,this.getPaginatedObjects(t)):Promise.resolve([])},prevPage:function(){return this.pagination.page>1?(this.pagination.page=this.pagination.page-1,this.getPaginatedObjects()):Promise.resolve()},setPageSize:function(t){this.pagination.page_size=t,this.pagination.page=1}}};var p={mixins:[h],components:{StaggeredList:s},props:{relationName:{type:String,required:!0},viewVisibility:{type:Boolean,default:function(){return!1}},addedRelations:{type:Array,default:function(){return[]}},hideRelations:{type:Array,default:function(){return[]}}},computed:{keyEvents:function(){return{esc:{keyup:this.handleKeyboard}}}},data:function(){return{method:"relationshipsJson",loading:!1,pendingRelations:[],relationsData:[],isVisible:!1}},created:function(){this.endpoint="".concat(this.method,"/").concat(this.relationName)},watch:{addedRelations:function(t){this.pendingRelations=t},pendingRelations:function(t){this.relationsData=this.relationFormatterHelper(t)},viewVisibility:function(t){this.isVisible=t},isVisible:function(){var t=this;this.objects.length||this.loadObjects(),this.$nextTick(function(){t.isVisible&&t.$refs.inputFilter&&t.$refs.inputFilter.focus()}),this.$emit("visibility-setter",this.isVisible)},loading:function(t){this.$parent.$emit("loading",t)}},methods:{loadObjects:function(){var t,e=(t=regeneratorRuntime.mark(function t(){var e;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return this.loading=!0,t.next=3,this.getPaginatedObjects();case 3:return e=t.sent,this.loading=!1,t.abrupt("return",e);case 6:case"end":return t.stop()}},t,this)}),function(){var e=this,n=arguments;return new Promise(function(i,a){var o=t.apply(e,n);function r(t,e){try{var n=o[t](e),r=n.value}catch(t){return void a(t)}n.done?i(r):Promise.resolve(r).then(s,c)}function s(t){r("next",t)}function c(t){r("throw",t)}s()})});return function(){return e.apply(this,arguments)}}(),appendRelations:function(){this.$emit("append-relations",this.pendingRelations),this.isVisible=!1},handleKeyboard:function(t){this.isVisible&&(t.stopImmediatePropagation(),t.preventDefault(),this.hideRelationshipModal())},hideRelationshipModal:function(){this.pendingRelations=this.addedRelations,this.isVisible=!1},hasElementsToShow:function(){var t=this;return this.objects.filter(function(e){return!t.hideRelations.filter(function(t){return e.id===t.id}).length}).length},relationFormatterHelper:function(t,e){var n="";try{n=JSON.stringify(t)}catch(t){console.error(t)}return n},containsId:function(t,e){return t.filter(function(t){return t.id===e}).length}}},f=(n(88),{name:"tree-list",template:'\n        <div\n            class="tree-list-node"\n            :class="treeListMode">\n\n            <div v-if="!isRoot">\n                <div v-if="multipleChoice"\n                    class="node-element"\n                    :class="{\n                        \'tree-related-object\': isRelated,\n                        \'disabled\': isCurrentObjectInPath,\n                        \'node-folder\': isFolder,\n                    }">\n\n                    <span\n                        @click.prevent.stop="toggle"\n                        class="icon"\n                        :class="nodeIcon"\n                        ></span>\n                    <input\n                        type="checkbox"\n                        :value="item"\n                        v-model="related"\n                    />\n                    <label\n                        @click.prevent.stop="toggle"\n                        :class="isFolder ? \'is-folder\' : \'\'"><: caption :></label>\n                </div>\n                <div v-else class="node-element"\n                    :class="{\n                        \'tree-related-object\': isRelated || stageRelated,\n                        \'was-related-object\': isRelated && !stageRelated,\n                        \'disabled\': isCurrentObjectInPath\n                    }"\n\n                    @click.prevent.stop="select">\n                    <span\n                        @click.prevent.stop="toggle"\n                        class="icon"\n                        :class="nodeIcon"\n                        ></span>\n                    <label><: caption :></label>\n                </div>\n            </div>\n            <div :class="isRoot ? \'\' : \'node-children\'" v-show="open" v-if="isFolder">\n                <tree-list\n                    @add-relation="addRelation"\n                    @remove-relation="removeRelation"\n                    @remove-all-relations="removeAllRelations"\n                    v-for="(child, index) in item.children"\n                    :key="index"\n                    :item="child"\n                    :multiple-choice="multipleChoice"\n                    :related-objects="relatedObjects"\n                    :object-id=objectId>\n                </tree-list>\n            </div>\n        </div>\n    ',data:function(){return{stageRelated:!1,related:!1,open:!0}},props:{multipleChoice:{type:Boolean,default:!0},captionField:{type:String,required:!1,default:"name"},childrenField:{type:String,required:!1,default:"children"},item:{type:Object,required:!0,default:function(){}},relatedObjects:{type:Array,default:function(){return[]}},objectId:{type:String,required:!1}},computed:{caption:function(){return this.item[this.captionField]},isFolder:function(){return this.item.children&&!!this.item.children.length},isRoot:function(){return this.item.root||!1},isRelated:function(){var t=this;return!!this.item.id&&!!this.relatedObjects.filter(function(e){return e.id===t.item.id}).length},isCurrentObjectInPath:function(){return this.item&&this.item.object&&-1!==this.item.object.meta.path.indexOf(this.objectId)},nodeIcon:function(){var t="";return t+=this.isFolder?this.open?"icon-down-dir":"icon-right-dir":"unicode-branch"},treeListMode:function(){var t=[];return this.isRoot&&t.push("root-node"),this.multipleChoice?t.push("tree-list-multiple-choice"):t.push("tree-list-single-choice"),this.isCurrentObject&&t.push("disabled"),t.join(" ")}},watch:{related:function(t){this.stageRelated=t},stageRelated:function(t){this.item.object&&(t?this.$emit("add-relation",this.item.object):this.$emit("remove-relation",this.item.object))},relatedObjects:function(){this.related=this.isRelated}},methods:{toggle:function(){this.isFolder&&(this.open=!this.open)},addRelation:function(t){this.$emit("add-relation",t)},removeRelation:function(t){this.$emit("remove-relation",t)},removeAllRelations:function(){this.$emit("remove-all-relations")},select:function(){this.isCurrentObjectInPath||(this.$emit("remove-all-relations"),this.stageRelated=!this.stageRelated)}}}),m=n(16);var g={extends:p,components:{TreeList:f},props:{relatedObjects:{type:Array,default:function(){return[]}},loadOnStart:[Boolean,Number],multipleChoice:{type:Boolean,default:!0}},data:function(){return{jsonTree:{}}},created:function(){this.loadTree()},watch:{pendingRelations:function(t){var e=this,n=t.filter(function(t){return!e.isRelated(t.id)});this.multipleChoice||n.length&&(n=n[0]),this.relationsData=this.relationFormatterHelper(n);var i=this.relatedObjects.filter(function(t){return!e.isPending(t.id)});this.$emit("remove-relations",i)},objects:function(){var t=this;this.pendingRelations=this.objects.filter(function(e){return t.isRelated(e.id)})}},methods:{loadTree:function(){var t,e=(t=regeneratorRuntime.mark(function t(){var e;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(!this.loadOnStart){t.next=7;break}return e="number"==typeof this.loadOnStart?this.loadOnStart:0,t.next=4,Object(m.a)(e);case 4:return t.next=6,this.loadObjects();case 6:this.jsonTree={name:"Root",root:!0,object:{},children:this.createTree()};case 7:case"end":return t.stop()}},t,this)}),function(){var e=this,n=arguments;return new Promise(function(i,a){var o=t.apply(e,n);function r(t,e){try{var n=o[t](e),r=n.value}catch(t){return void a(t)}n.done?i(r):Promise.resolve(r).then(s,c)}function s(t){r("next",t)}function c(t){r("throw",t)}s()})});return function(){return e.apply(this,arguments)}}(),addRelation:function(t){t&&void 0!==!t.id?this.containsId(this.pendingRelations,t.id)||this.pendingRelations.push(t):console.error("[addRelation] needs first param (related) as {object} with property id set")},removeRelation:function(t){t&&t.id?this.pendingRelations=this.pendingRelations.filter(function(e){return e.id!==t.id}):console.error("[removeRelation] needs first param (related) as {object} with property id set")},removeAllRelations:function(){this.pendingRelations=[],this._setChildrenData(this,"stageRelated",!1)},_setChildrenData:function(t,e,n){var i=this;void 0!==t&&e in t&&(t[e]=n),t.$children.forEach(function(t){i._setChildrenData(t,e,n)})},createTree:function(){var t=this,e=[];return this.objects.forEach(function(n){var i=n.meta.path&&n.meta.path.split("/");if(i.length){i.shift();var a=e;i.forEach(function(e){var i=t.findPath(a,e);if(i)a=i.children;else{var o=n;o.id!==e&&(o=t.findObjectById(e));var r={id:e,related:t.isRelated(e),name:o.attributes.title||"",object:o,children:[]};a.push(r),a=r.children}})}}),e},findPath:function(t,e){var n=t.filter(function(t){return t.id===e});return!!n.length&&n[0]},isRelated:function(t){return!!this.relatedObjects.filter(function(e){return t===e.id}).length},isPending:function(t){return!!this.pendingRelations.filter(function(e){return t===e.id}).length}}};function v(t){return function(){var e=this,n=arguments;return new Promise(function(i,a){var o=t.apply(e,n);function r(t,e){try{var n=o[t](e),r=n.value}catch(t){return void a(t)}n.done?i(r):Promise.resolve(r).then(s,c)}function s(t){r("next",t)}function c(t){r("throw",t)}s()})}}var b,R,y,w={mixins:[h],components:{StaggeredList:s,RelationshipsView:p,TreeView:g},props:{relationName:{type:String,required:!0},loadOnStart:[Boolean,Number],multipleChoice:{type:Boolean,default:!0},configPaginateSizes:{type:String,default:"[]"}},data:function(){return{method:"relatedJson",loading:!1,count:0,removedRelated:[],addedRelations:[],relationsData:[],newRelationsData:[],pageSize:u.page_size}},computed:{alreadyInView:function(){var t=this.addedRelations.map(function(t){return t.id}),e=this.objects.map(function(t){return t.id});return t.concat(e)},paginateSizes:function(){return JSON.parse(this.configPaginateSizes)}},created:function(){this.endpoint="".concat(this.method,"/").concat(this.relationName)},mounted:function(){this.loadOnMounted()},watch:{pageSize:function(t){this.setPageSize(t),this.loadRelatedObjects()},loading:function(t){this.$emit("loading",t)}},methods:{loadOnMounted:(y=v(regeneratorRuntime.mark(function t(){var e;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:if(!this.loadOnStart){t.next=6;break}return e="number"==typeof this.loadOnStart?this.loadOnStart:0,t.next=4,Object(m.a)(e);case 4:return t.next=6,this.loadRelatedObjects();case 6:case"end":return t.stop()}},t,this)})),function(){return y.apply(this,arguments)}),loadRelatedObjects:(R=v(regeneratorRuntime.mark(function t(){var e;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return this.loading=!0,t.next=3,this.getPaginatedObjects();case 3:return e=t.sent,this.loading=!1,this.$emit("count",this.pagination.count),t.abrupt("return",e);case 7:case"end":return t.stop()}},t,this)})),function(){return R.apply(this,arguments)}),relationToggle:function(t){t&&t.id?this.containsId(this.removedRelated,t.id)?this.undoRemoveRelation(t):this.removeRelation(t):console.error("[reAddRelations] needs first param (related) as {object} with property id set")},removeRelation:function(t){this.removedRelated.push(t),this.relationsData=JSON.stringify(this.removedRelated)},undoRemoveRelation:function(t){this.removedRelated=this.removedRelated.filter(function(e){return e.id!==t.id}),this.relationsData=JSON.stringify(this.removedRelated)},setRemovedRelated:function(t){t&&(this.removedRelated=t,this.relationsData=JSON.stringify(this.removedRelated))},toPage:(b=v(regeneratorRuntime.mark(function t(e){var n;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return this.loading=!0,t.next=3,h.methods.toPage.call(this,e);case 3:return n=t.sent,this.loading=!1,t.abrupt("return",n);case 6:case"end":return t.stop()}},t,this)})),function(t){return b.apply(this,arguments)}),removeAddedRelations:function(t){t?this.addedRelations=this.addedRelations.filter(function(e){return e.id!==t}):console.error("[removeAddedRelations] needs first param (id) as {Number|String}")},appendRelations:function(t){if(this.addedRelations.length)for(var e=this.addedRelations.map(function(t){return t.id}),n=0;n<t.length;n++)e.indexOf(t[n].id)<0&&this.addedRelations.push(t[n]);else this.addedRelations=t;this.newRelationsData=JSON.stringify(this.addedRelations)},containsId:function(t,e){return t.filter(function(t){return t.id===e}).length},buildViewUrl:function(t,e){return"".concat(window.location.protocol,"//").concat(window.location.host,"/").concat(t,"/view/").concat(e)},requestPanel:function(){this.$parent.$parent.$emit("request-panel",{relation:{name:this.relationName,alreadyInView:this.alreadyInView}})}}},O={components:{PropertyView:{components:{RelationView:w},props:{tabOpen:{type:Boolean,default:!0},isDefaultOpen:{type:Boolean,default:!1}},data:function(){return{isOpen:!0,isLoading:!1,count:0}},mounted:function(){this.isOpen=this.isDefaultOpen},watch:{tabOpen:function(){this.isOpen=this.tabOpen}},methods:{toggleVisibility:function(){this.isOpen=!this.isOpen},onToggleLoading:function(t){this.isLoading=t},onCount:function(t){this.count=t}}},RelationView:w},data:function(){return{tabsOpen:!0}},computed:{keyEvents:function(){return{esc:{keyup:this.toggleTabs}}}},methods:{toggleTabs:function(){return this.tabsOpen=!this.tabsOpen}}},j=n(74),P=n.n(j);var S={mixins:[h],props:{relationName:{type:String,default:""},alreadyInView:{type:Array,default:function(){return[]}}},data:function(){return{method:"relationshipsJson",endpoint:"",selectedObjects:[]}},computed:{relationHumanizedName:function(){return P()(this.relationName)}},watch:{relationName:{immediate:!0,handler:function(t,e){t&&(this.selectedObjects=[],this.endpoint="".concat(this.method,"/").concat(t),this.loadObjects())}}},methods:{returnData:function(){var t={objects:this.selectedObjects,relationName:this.relationName};this.$root.onRequestPanelToggle({returnData:t})},toggle:function(t,e){var n=this.selectedObjects.indexOf(t);-1!=n?this.selectedObjects.splice(n,1):this.selectedObjects.push(t)},isAlreadyRelated:function(){return!0},loadObjects:function(){var t,e=(t=regeneratorRuntime.mark(function t(){var e;return regeneratorRuntime.wrap(function(t){for(;;)switch(t.prev=t.next){case 0:return this.loading=!0,t.next=3,this.getPaginatedObjects();case 3:return e=t.sent,this.loading=!1,t.abrupt("return",e);case 6:case"end":return t.stop()}},t,this)}),function(){var e=this,n=arguments;return new Promise(function(i,a){var o=t.apply(e,n);function r(t,e){try{var n=o[t](e),r=n.value}catch(t){return void a(t)}n.done?i(r):Promise.resolve(r).then(s,c)}function s(t){r("next",t)}function c(t){r("throw",t)}s()})});return function(){return e.apply(this,arguments)}}()}},k=n(73),x=n.n(k),I=(n(77),{enableTime:!1,dateFormat:"Y-m-d H:i",altInput:!0,altFormat:"F j, Y - H:i"}),N={install:function(t){t.directive("datepicker",{inserted:function(t,e,n){var i=I;n.data&&n.data.attrs&&n.data.attrs.time&&(i.enableTime=n.data.attrs.time);try{var a=x()(t,i),o=document.createElement("span");o.classList.add("clear-button"),o.innerHTML="&times;",o.addEventListener("click",function(){a.clear()}),t.parentElement.appendChild(o)}catch(t){console.error(t)}}})}},A=n(72),T=n.n(A),B=(n(76),{mode:"code",modes:["tree","code"],history:!0,search:!0,onChange:function(t){if(t){var e=t.jsonEditor.get();try{t.value=JSON.stringify(e)}catch(t){console.error(t)}}}}),C={install:function(t){t.directive("jsoneditor",{inserted:function(t,e,n,i){var a=t.value;try{var o=JSON.parse(a)||{};if(o){t.style.display="none";var r=document.createElement("div");r.className="jsoneditor-container",t.parentElement.insertBefore(r,t),t.jsonEditor=new T.a(r,B),t.jsonEditor.set(o)}}catch(t){console.error(t)}}})}},$={devtools:!0},L={delimiters:["<:",":>"]},E={configFull:{toolbar:[{name:"document",groups:["mode"],items:["Source"]},{name:"basicstyles",groups:["basicstyles","cleanup"],items:["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"]},{name:"paragraph",groups:["list","blocks","align"],items:["NumberedList","BulletedList","-","Blockquote","JustifyLeft","JustifyCenter","JustifyRight","JustifyBlock"]},{name:"links",items:["Link","Unlink","Anchor"]},{name:"editAttributes",items:["Attr"]},{name:"editing",groups:["find"],items:["Find","Replace"]},{name:"insert",items:["Image","Table","HorizontalRule","SpecialChar","Formula"]},{name:"tools",items:["ShowBlocks","AutoCorrect"]},{name:"styles",items:["Format","Styles"]},{name:"clipboard",groups:["clipboard","undo"],items:["Cut","Copy","Paste","PasteText","PasteFromWord","-","Undo","Redo"]}],allowedContent:!0,language:BEDITA.currLang2,entities:!1,fillEmptyBlocks:!1,forcePasteAsPlainText:!0,startupOutlineBlocks:!0},configNormal:{toolbar:[{name:"document",groups:["mode"],items:["Source"]},{name:"basicstyles",groups:["basicstyles","cleanup"],items:["Bold","Italic","Underline","Strike","-","RemoveFormat"]},{name:"links",items:["Link","Unlink"]},{name:"clipboard",groups:["clipboard","undo"],items:["PasteText","PasteFromWord","-","Undo","Redo"]}],allowedContent:!0,language:BEDITA.currLang2,entities:!1,fillEmptyBlocks:!1,forcePasteAsPlainText:!0,startupOutlineBlocks:!0},configSimple:{toolbar:[{name:"document",groups:["mode"],items:["Source"]},{name:"basicstyles",groups:["basicstyles","cleanup"],items:["Bold","Italic","Underline","Strike","Subscript","Superscript","-","RemoveFormat"]},{name:"links",items:["Link","Unlink"]},{name:"clipboard",groups:["clipboard","undo"],items:["Undo","Redo"]},{name:"tools",items:["ShowBlocks"]}],allowedContent:!0,language:BEDITA.currLang2,entities:!1,fillEmptyBlocks:!1,forcePasteAsPlainText:!0,startupOutlineBlocks:!0}},F={install:function(t){t.directive("richeditor",{inserted:function(t){var e=t.getAttribute("ckconfig"),n=null;E&&(n=E[e]),CKEDITOR.replace(t,n)}})}},D=n(71),V=n.n(D);for(var J in a.a.use(C),a.a.use(N),a.a.use(F),a.a.use(V.a),$)$.hasOwnProperty(J)&&(a.a.config[J]=$[J]);for(var z in L)L.hasOwnProperty(z)&&(a.a.options[z]=L[z]);var _=new a.a({el:"main",components:{ModulesIndex:o,ModulesView:O,TrashIndex:o,TrashView:O,RelationsAdd:S},data:function(){return{vueLoaded:!1,urlPagination:"",searchQuery:"",pageSize:"100",page:"",sort:"",panelIsOpen:!1,addRelation:{}}},created:function(){this.vueLoaded=!0,this.loadUrlParams()},methods:{onRequestPanelToggle:function(t){var e=this;this.panelIsOpen=!this.panelIsOpen;var n=document.querySelector("html").classList;n.contains("is-clipped")?n.remove("is-clipped"):n.add("is-clipped"),t.returnData&&t.returnData.relationName&&this.$refs.moduleView.$refs[t.returnData.relationName].$refs.relation.appendRelations(t.returnData.objects),this.panelIsOpen&&t.relation&&t.relation.name?this.addRelation=t.relation:Object(m.a)(500).then(function(){return e.addRelation={}})},loadUrlParams:function(){if(window.location.search){var t=window.location.search,e=/[?&]q=([^&#]*)/g,n=t.match(e);n&&n.length&&(n=n.map(function(t){return t.replace(e,"$1")}),this.searchQuery=n[0]);var i=/[?&]page_size=([^&#]*)/g;(n=t.match(i))&&n.length&&(n=n.map(function(t){return t.replace(i,"$1")}),this.pageSize=this.isNumeric(n[0])?n[0]:"");var a=/[?&]page=([^&#]*)/g;(n=t.match(a))&&n.length&&(n=n.map(function(t){return t.replace(a,"$1")}),this.page=this.isNumeric(n[0])?n[0]:"");var o=/[?&]sort=([^&#]*)/g;(n=t.match(o))&&n.length&&(n=n.map(function(t){return t.replace(o,"$1")}),this.sort=n[0])}},buildUrlParams:function(t){var e="".concat(window.location.origin).concat(window.location.pathname),n=!0;return Object.keys(t).forEach(function(i){t[i]&&""!==t[i]&&(e+="".concat(n?"?":"&").concat(i,"=").concat(t[i]),n=!1)}),e},updatePagination:function(){window.location.replace(this.urlPagination)},search:function(){this.page="",this.applyFilters()},resetResearch:function(){this.searchQuery="",this.applyFilters()},applyFilters:function(){var t=this.buildUrlParams({q:this.searchQuery,page_size:this.pageSize,page:this.page,sort:this.sort});window.location.replace(t)},isNumeric:function(t){return!isNaN(t)}}});window._vueInstance=_}},[[132,0,1]]]);