<!-- fieldAwesome.vue -->
<template>
    <div :id="schema.model">
        <div class="field-st-upload">
            <div class="ts-upload-input-group">
                <input
                    class="form-control"
                    type="text"
                    v-model="value"
                    :disabled="disabled"
                    :maxlength="schema.max"
                    :placeholder="schema.placeholder"
                    :readonly="schema.readonly" >
                <button @click="openUploader" class="button button-primary"><i class="fa fa-plus"></i></button>
            </div>
            <button v-if="value" @click="clearImage" class="button button-remove"><i class="fa fa-minus"></i></button>
            <div class="ts-media-wrap" v-if="showImg">
                <img v-if="value" :src="value" alt="">
            </div>
        </div>
        <div class="hint error" v-if="errorMessage" v-html="hintMessage"></div>
        <div class="hint" v-html="hintHtml"></div>
    </div>
</template>

<script>
    import { abstractField } from "vue-form-generator";

    export default {
        mixins: [ abstractField ],
        data(){
            return {
                fileManager:{},
                hintHtml: "",
                showImg: true,
                hintMessage: '',
                errorMessage: false,
            }
        },
        created(){
            let app=this;
            setTimeout(function () {
                app.checkEnableField();
            }, 100);
            let id= this.schema.customModel ? 'ts'+this.schema.customModel : 'ts'+this.schema.model;
            let allowType = this.schema.allow_type || false;
            let libraryType = ['image'];
            if ( allowType && allowType instanceof Array ) {
                libraryType = allowType
            }
            if ( app.value ) {
                let ext = app.getExtensionFromUrl( app.value );
                app.checkShowImg(ext);
            }
            if(typeof wp.media.frames[id] === 'undefined'){
                wp.media.frames[id] = wp.media({
                    title: 'Select image',
                    multiple: false,
                    library: {
                        type: libraryType
                    },
                    button: {
                        text: 'Use selected'
                    }
                });
            }

            if(this.schema.v_hint == 'yes'){
                this.hintHtml = this.schema.desc;
            }

        },
        methods:{
            openUploader()
            {
                let id=this.schema.customModel ? 'ts'+this.schema.customModel : 'ts'+this.schema.model;
                let app=this;
                if(wp.media.frames[id]) {
                    wp.media.frames[id].open();

                    wp.media.frames[id].on('select', function(){
                        var selection =wp.media.frames[id].state().get('selection');

                        // no selection
                        if (!selection) {
                            return;
                        }
                        // iterate through selected elements
                        selection.each(function(attachment) {
                            let url = attachment.attributes.url;
                            let ext = app.getExtensionFromUrl(url);
                            let allowType = app.schema.allow_type || false;
                            if ( allowType && allowType instanceof Array ) {
                                if ( allowType.indexOf( ext ) != -1 ) {
                                    app.hintMessage = '';
                                    app.errorMessage = false;
                                    app.value = url;
                                    app.checkShowImg(ext);
                                } else {
                                    app.hintMessage = app.schema.message;
                                    app.errorMessage = true;
                                }
                            } else {
                                app.value = url;
                            }
                        });
                    });
                    return;
                }
            },
            clearImage(){
                //this.$emit('value','');
                this.value='';
            },
            checkEnableField(){
                let condition = this.schema.condition;
                if(typeof condition != 'undefined' && condition != ''){
                    let arr_cond = condition.split(',');
                    if(arr_cond.length){
                        let c = 0;
                        for(let i = 0; i< arr_cond.length; i++){
                            let arr_sub = arr_cond[i].split(':is');
                            if(typeof this.schema.model != 'undefined' && this.schema.model != null) {
                                if ('(' + this.model[arr_sub[0]] + ')' != arr_sub[1]) {
                                    c++;
                                }
                            }
                        }

                        let cComp = document.getElementById(this.schema.model);
                        if(cComp != null) {
                            if (c > 0) {
                                cComp.parentNode.parentNode.classList.add("st-admin-hidden");
                            } else {
                                cComp.parentNode.parentNode.classList.remove("st-admin-hidden");
                            }
                        }
                    }
                }
            },
            getExtensionFromUrl( url ) {
                return url.split(/\#|\?/)[0].split('.').pop().trim();
            },
            checkShowImg( ext ) {
                if ( ext == 'svg' || ext == 'jpg' || ext == 'png' || ext == 'jpeg' ) {
                    this.showImg = true;
                } else {
                    this.showImg = false;
                }
            }
        },
        watch: {
            model:{
                handler: function (after, before) {
                    this.checkEnableField();
                },
                deep: true
            },
        }
    };
</script>
