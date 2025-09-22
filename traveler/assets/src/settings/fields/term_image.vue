<template>
    <div :id="schema.model">
       <div :class="loading + ' gmz-term-image'">
           <div class="gmz-loading"><span>Loading...</span></div>
            <div class="gmz-select-tax">
                <p class="label">Select taxonomy</p>
                <select class="form-control" v-model="taxSelected">
                    <option value="-1">--- Select ---</option>
                    <option :value="index" v-for="(item, index) in schema.options">{{ item.name }}</option>
                </select>
            </div>
           <div class="gmz-render-terms" v-if="terms.length">
                <div class="item" v-for="(item, index) in terms">
                    <div class="term-upload">
                        <div class="upload-handle" @click="openUploader(index, item.term_id)">+</div>
                        <img :src="item.image_url" :alt="item.name" v-if="item.image_url != ''"/>
                        <div class="close" v-if="item.image_url != ''" @click="removeImage(index, item.term_id)">+</div>
                    </div>
                    <div class="term-name">{{ item.name }}</div>
                </div>
           </div>
       </div>
    </div>
</template>

<script>
    import { abstractField } from "vue-form-generator";
    import API from '../api';
    export default {
        mixins: [ abstractField ],
        data(){
            return {
                taxSelected: '-1',
                terms: [],
                loading: ''
            }
        },
        created() {
            this.taxSelected = this.value;
        },
        methods:{
            removeImage(index, term_id){
                this.terms[index].image_id = '';
                this.terms[index].image_url = '';
                API.updateTermsAjax(term_id, '', '', 'delete');
            },
            openUploader(item_index, term_id)
            {
                let id='ts'+this.schema.model + '_' + item_index;
                if(typeof wp.media.frames[id] === 'undefined'){
                    wp.media.frames[id] = wp.media({
                        title: 'Select image',
                        multiple: false,
                        library: {
                            type: 'image'
                        },
                        button: {
                            text: 'Use selected image'
                        }
                    });
                }

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
                            let img_url = attachment.attributes.url;
                            let img_id = attachment.attributes.id;
                            app.terms[item_index].image_id = img_id;
                            app.terms[item_index].image_url = img_url;
                            API.updateTermsAjax(term_id, img_id, img_url, 'update');
                        });
                    });
                    return;
                }
            },
            renderTermItems(term){
                let app=this;
                API.getTermsAjax(term).then(function (rs) {
                    let data=rs.data;
                    app.terms=data.rows;
                    app.loading = '';
                })
            }
        },
        watch: {
            taxSelected:{
                handler: function (after, before) {
                    if(after !== '-1') {
                        this.loading = 'loading';
                        this.renderTermItems(after);
                    }else{
                        this.terms = [];
                    }
                    this.value = after;
                },
                deep: true
            }
        },
        components:{

        }
    };
</script>
<style lang="scss">
    .gmz-term-image{
        position: relative;
        .gmz-loading{
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.6);
            text-align: center;
            z-index: 12;
            font-size: 15px;
            font-weight: 500;
            opacity: 0;
            visibility: hidden;
            -webkit-transition: all 0.3s;
            -moz-transition: all 0.3s;
            -ms-transition: all 0.3s;
            -o-transition: all 0.3s;
            transition: all 0.3s;
            span{
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
            }
        }
        &.loading{
            .gmz-loading{
                opacity: 1;
                visibility: visible;
            }
        }
        .gmz-select-tax{
            .label{
                margin-top: 0;
                margin-bottom: 7px;
                font-weight: 500;
            }
            select{
                max-width: 200px;
            }
        }
        .gmz-render-terms{
            border: 1px solid #dfdfdf;
            padding: 20px;
            border-radius: 3px;
            .item{
                border-bottom: 1px solid #dfdfdf;
                padding-bottom: 10px;
                margin-bottom: 10px;
                display: flex;
                align-items: center;
                .term-name{
                    font-size: 15px;
                    font-weight: 500;
                }
                .term-upload{
                    cursor: pointer;
                    width: 70px;
                    height: 70px;
                    border: 2px dashed #dfdfdf;
                    border-radius: 8px;
                    text-align: center;
                    font-size: 41px;
                    font-weight: 500;
                    color: #666;
                    line-height: 57px;
                    margin-right: 20px;
                    position: relative;
                    .upload-handle{
                        position: absolute;
                        z-index: 9;
                        left: 50%;
                        top: 50%;
                        width: 100%;
                        height: 100%;
                        transform: translate(-50%, -50%);
                    }
                    .close{
                        color: #fff;
                        background: #cc0000;
                        height: 17px;
                        width: 17px;
                        border-radius: 50%;
                        position: absolute;
                        top: -8px;
                        right: -8px;
                        font-size: 16px;
                        line-height: 14px;
                        text-align: center;
                        transform: rotate(45deg);
                        cursor: pointer;
                        z-index: 10;
                    }
                    img{
                        position: absolute;
                        top: 0;
                        left: 0;
                        width: 100%;
                        height: 100%;
                        object-fit: cover;
                        border-radius: 8px;
                    }
                }
                &:last-child{
                    border-bottom: none;
                    margin-bottom: 0;
                    padding-bottom: 0;
                }
            }
        }
    }
</style>