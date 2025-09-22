<template>
    <div :id="schema.model">
       <div class="gmz-select-ui">
            <div class="col-left">
                <p class="col-label" v-if="typeof schema.left_label !== 'undefined'">{{ schema.left_label }}</p>
                <div class="gmz-item" v-for="(item, index) in schema.choices" :value="item.value">
                    <span class="label">{{ item.label }}</span>
                    <span  class="action" @click="selectItem(item)">Select</span>
                </div>
            </div>
           <div class="col-right">
               <p class="col-label" v-if="typeof schema.right_label !== 'undefined'">{{ schema.right_label }}</p>
               <draggable v-model="value" @start="drag=true" @end="drag=false">
                   <div class="gmz-item" v-if="value.length > 0" v-for="(item, index) in value" :value="item.value">
                       <span class="label">{{ item.label }}</span>
                       <span  class="action" @click="removeItem(item)">Remove</span>
                   </div>
               </draggable>
           </div>
       </div>
    </div>
</template>

<script>
    import { abstractField } from "vue-form-generator";
    import draggable from 'vuedraggable'
    export default {
        mixins: [ abstractField ],
        data(){
            return {
            }
        },
        created() {
            let app = this;
            if(this.value == ''){
                this.value = this.schema.std;
            }
            this.renderChoiceFirst(this.value);
        },
        methods:{
            renderChoiceFirst(std){
                var temp = this.schema.choices;
                for(var i = 0; i < Object.keys(std).length; i++){
                    var currentKey = std[i].value;
                    for(var j = 0; j < Object.keys(this.schema.choices).length; j++){
                        if(this.schema.choices[j].value === currentKey){
                            this.schema.choices.splice(j, 1);
                        }
                    }
                }
            },
            selectItem(item){
                if(typeof this.schema.max_choice != 'undefined'){
                    if(Object.keys(this.value).length >= this.schema.max_choice){
                        alert('Maximum number is selected: ' + this.schema.max_choice)
                        return;
                    }
                }

                this.value.push(item);
                for(var i = 0; i < Object.keys(this.schema.choices).length; i++){
                    if(this.schema.choices[i].value === item.value){
                       this.schema.choices.splice(i, 1);
                    }
                }


            },
            removeItem(item){
                this.schema.choices.push(item);
                for(var i = 0; i < Object.keys(this.value).length; i++){
                    if(this.value[i].value === item.value){
                        this.value.splice(i, 1);
                    }
                }
            }
        },
        watch: {

        },
        components:{
            draggable
        }
    };
</script>
<style lang="scss">
    .gmz-select-ui{
        border: 1px solid #dfdfdf;
        border-radius: 3px;
        display: flex;
        -ms-flex-wrap: wrap;
        flex-wrap: wrap;
        .col-label{
            color: #2a2a2a;
            margin-top: 0;
            font-weight: 500;
            font-size: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 25px;
        }
        .col-left{
            width: 50%;
            border-right: 1px solid #dfdfdf;
            padding: 25px;
            flex-basis: 0;
            -ms-flex-positive: 1;
            flex-grow: 1;
            max-width: 100%;
        }
        .col-right{
            width: 50%;
            padding: 25px;
            height: 100%;
            flex-basis: 0;
            -ms-flex-positive: 1;
            flex-grow: 1;
            max-width: 100%;
        }
        .gmz-item{
            border: 1px solid #dfdfdf;
            padding: 11px;
            margin-top: 10px;
            margin-bottom: 10px;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            .label{
                color: #2a2a2a;
                font-weight: 500;
                font-size: 14px;
            }
            .action{
                color: #fff;
                background: #0a97c2;
                padding: 3px 5px;
                font-weight: 500;
                border-radius: 3px;
                cursor: pointer;
            }
        }
    }
</style>