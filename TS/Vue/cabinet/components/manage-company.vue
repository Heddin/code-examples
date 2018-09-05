<template>
    <b-container>
        <b-row>
            <b-col cols="3" sm="auto" class="left-block">
                <div class="company-logo"
                     @mouseover="hovered = true"
                     @mouseleave="hovered = false"
                     @click="openDialog">
                    <img :src="company.logo" :alt="company.name">
                   <transition name="fade">
                    <p v-if="hovered" class="hover-text">ИЗМЕНИТЬ</p>
                   </transition>
                    <input style="display: none" type="file" id="logo" accept="image/*" name="logo" @change="onLogoChanged" ref="logoField">
                </div>
                <p class="company-name">

                    <transition name="fade" mode="out-in">
                      <span v-if="!editing">
                       <span>{{company.name}}</span>
                       <img src="/assets/img/pencil.png" alt="edit" class="btn-like" @click="editing = !editing"/>
                      </span>
                      <span v-if="editing">
                       <input id="name" name="name" v-model="newName" placeholder="Новое название">
                       <img src="/assets/img/list-done.png" class="btn-like" alt="save" @click="saveName">
                      </span>
                    </transition>
                </p>
                <hr>
                <p align="left">
                    <span>Количество пользователей</span>
                    <span style="float: right">{{company.count_users}}</span>
                </p>
                <p align="left">
                    <span>Количество Курсов</span>
                    <span style="float: right">{{company.count_courses}}</span>
                </p>
            </b-col>
            <b-col cols=7 class="right-block">
                <div class="form-item">
                    <p class="company-name hdr">Данные компании:</p>
                </div>
                <hr>
                <div class="form-item">
                    <p>E-mail</p>
                    <p class="cur-val email">{{company.email}}</p>
                    <input type="email" name="new-email" class="form-control" @change=updCompany placeholder="Введите новый Е-mail"
                           v-model="newEmail" title=""/>
                </div>
                <hr>
                <div class="form-item">
                    <p>Транслитерация названия</p>
                    <p class="cur-val slug">{{company.slug}}</p>
                    <input type="text" name="slug" class="form-control" @change=updCompany placeholder="Введите новую транслитерацию"
                           v-model="newSlug"/>
                </div>
                <hr>
                <div class="form-item">
                   <!--<button @click=updCompany() class="company-save">Сохранить изменения</button>&ndash;&gt;-->
                   <!--<br>-->
                   <a id="remove" @click="deleteCompany()">Удалить Компанию</a>
                </div>
            </b-col>
        </b-row>
    </b-container>
</template>

<script lang="ts">
    import {Component, Vue, Prop} from 'vue-property-decorator';
    import BootstrapVue from 'bootstrap-vue';

    Vue.use(BootstrapVue);

    const CONFIRM = `Вы действительно хотите удалить Компанию?
    Восстановление будет невозможно.`;

    @Component
    export default class ManageCompany extends Vue {


        private meta;

        public newEmail = "";
        public newSlug = "";
        public newLogo = {file:"",name:""};
        public newName = "";


        public editing = false;
        public hovered = false;

        constructor(opts) {
            super(opts);
            this.meta = <HTMLMetaElement>document.querySelector("meta[name=csrf-token]");
        }

        get company() {
            return this.$store.state.company;
        }


        updCompany(): void {
                let data = new FormData();

                data.append("_token", this.meta.content);
                data.append("email", this.newEmail);
                data.append("slug", this.newSlug);
                data.append("name" , this.newName);
                data.append("logo" , this.newLogo.file, this.newLogo.name);

                this.$store.dispatch('updateCompany',data);
        }
        deleteCompany(): void {
                this.$store.dispatch('deleteCompany',CONFIRM);
        }
        saveName():void{
            this.updCompany();
            this.editing = !this.editing;
        }
        openDialog(){
            let logoField = <HTMLInputElement>this.$refs.logoField;
                logoField.click();
        }
        onLogoChanged(event){
            this.newLogo.file = event.srcElement.files[0];
            this.newLogo.name = event.srcElement.value;
            this.updCompany();
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .container {
        padding: 30px 15px;
    }
    .fade-enter-active, .fade-leave-active {
        transition: opacity .5s;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }
    .hover-text{
        color: #fff;
        position: absolute;
        top: 65px;
        right: 0;
        left: 0;
        font-size: 16px;
        font-family: Roboto;
        font-weight: bold;
    }
    .form-min-width{
        min-width: 291px !important;
    }
</style>
