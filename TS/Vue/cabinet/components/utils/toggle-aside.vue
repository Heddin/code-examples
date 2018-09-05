<template>
   <span @click="toggleAside">
    <div class="toggle-aside" ref="tElem"></div>
    <span class="ta-text"><slot></slot></span>
   </span>
</template>

<script lang="ts">
    import Vue from 'vue';
    import Component from 'vue-class-component';

    @Component
    export default class ToggleAside extends Vue{

            public opened: boolean = true;

            constructor(opts){
                super(opts);
            }

            toggleAside(){
                let aside = <HTMLDivElement>document.getElementById('aside');
                let tElem = <HTMLDivElement>this.$refs.tElem;

                console.log({aside,tElem});


                if(this.opened){
                    aside.classList.add('closed');
                    tElem.classList.add('toggle-open');
                    this.opened = false;
                }else{
                    aside.classList.remove('closed');
                    tElem.classList.remove('toggle-open');
                    this.opened = true;
                }
            }
    }
</script>

<style lang="scss" scoped>
    .ta-text{
        position: relative;
        top: -5px;
        cursor:pointer;
    }
    .toggle-aside{
        width: 36px;
        height: 24px;
        margin-left: -5px;
        display: inline-block;
        background: url(/assets/img/company/sidebar/sidebar-arrow-l.png) no-repeat center center;
    }
    .toggle-open{
        background: url(/assets/img/company/sidebar/sidebar-arrow-r.png) no-repeat center center;
    }
</style>
