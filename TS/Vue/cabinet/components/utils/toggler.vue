<template>
    <span @click.capture="toggle" class="toggler">
        <span v-show="closed">
          <span>Развернуть </span>
          <i class="fa fa-chevron-down"></i>
     </span>
    <span v-show="!closed">
          <span>Свернуть </span>
          <i class="fa fa-chevron-up"></i>
     </span>
   </span>
</template>

<script lang="ts">
    import {Component, Vue} from "vue-property-decorator";

    @Component
    export default class Toggler extends Vue{
        public closed: boolean = true;

        toggle(){
            this.closed = !this.closed;
        }
    }
</script>

<style scoped lang="scss">
    .toggler {
        -webkit-transition: color 0.3s ease;
        -moz-transition: color 0.3s ease;
        -ms-transition: color 0.3s ease;
        -o-transition: color 0.3s ease;
        transition: color 0.3s ease;

        position:relative;
        top:12px;
        right: -60px;

        color: #ccd6dc;
        &:hover{
            color: #5da100;
        }
    }
</style>