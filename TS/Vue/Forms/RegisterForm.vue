<template>
    <b-card>
        <template slot="header">
            <h3 class="form-header-text">Добро Пожаловать</h3>
            <div class="form-info-text">Для просмотра курсов, пожалуйста, зарегистрируйтесь</div>
        </template>
        <transition name="fade">
            <p class="errBag" v-show="err" ref="errBag"></p>
        </transition>
           <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Ваше имя"
                      label="Имя"
                      label-class=""
                      label-for="loginField"
                      :invalid-feedback="invalidName"
                      :state="nameState"
           >
                 <b-form-input  type="text"
                           placeholder="Имя Пользователя"
                           name="name"
                           id="reg_name"
                           title="Желаемое имя ползователя."
                           autocomplete="name"
                           v-model="user_name"
                           ref="nameField"
                 >

                 </b-form-input>
        </b-form-group>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Ваш Email"
                      label="Email"
                      label-class=""
                      label-for="loginField"
                      :invalid-feedback="invalidEmail"
                      :state="loginState"
        >
            <b-form-input  type="email"
                           placeholder="example@mail.com"
                           name="login"
                           id="reg_email"
                           title="Логин - Ваш email-адресс, используемый для входа на сайт."
                           autocomplete="email"
                           v-model="login"
                           ref="loginField"
            >

            </b-form-input>
        </b-form-group>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Ваш пароль"
                      label="Пароль"
                      label-class=""
                      label-for="passField"
                      :invalid-feedback="invalidPass"
                      :state="passState"
        >
            <b-form-input id="regPassField"
                          type="password"
                          placeholder="******"
                          title="Пароль должен быть не менее 6-ти символов длинной."
                          autocomplete="current-password"
                          v-model="pass"
                          @change="validatePass"
            >
            </b-form-input>
        </b-form-group>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Подтверждение пароля"
                      label="Повторите Пароль"
                      label-class=""
                      label-for="passField"
                      :invalid-feedback="passConfirmed"
                      :state="passConfirmState"
        >
            <b-form-input id="regPassConfirmField"
                          type="password"
                          placeholder="******"
                          title="Повторно введите пароль для подтверждения."
                          autocomplete="confirm-password"
                          v-model="passConfirm"
                          @change="confirmPass"
            >
            </b-form-input>
        </b-form-group>

        <div class="buttons-line">
            <a  v-show="isLoading  == 0" class="button button-limeade-approx" @click="register">Зарегистрироваться</a>
            <img v-show="isLoading == 1" src="/assets/img/loaders/green.gif" class="loader" ref="loginLoader">
            <img v-show="isLoading == 2" src="/assets/img/loaders/success.png" class="loader" ref="loginLoader">
            <!--<p class="forgot-pass">Забыли пароль?</p>-->
            <div class="btn-group">
                <a href="/social/google" title="Google+">
                                        <span class="soc-icon soc-google">
                                            <i class="google" aria-hidden="true"></i>
                                        </span>
                </a>
                <a href="/social/vkontakte" title="ВКонтакте">
                                        <span class="soc-icon soc-vk">
                                            <i class="vkontakte" aria-hidden="true"></i>
                                        </span>
                </a>
                <a href="/social/facebook" title="Facebook">
                                    <span class="soc-icon soc-fb">
                                        <i class="facebook" aria-hidden="true"></i>
                                    </span>
                </a>
            </div>
        </div>
        <div class="text-center">
            <div class="form-policies">Зарегистрировавшись, вы принимаете наши
                <a target="_blanc" class="" href="/user-agreement">Условия использования</a> и
                <a target="_blank" class="" href="/privacy-policy">Политику конфиденциальности</a>.
            </div>
        </div>
        <template slot="footer">
            <div class="text-center">
                <div class="sugg">
                    <span>Есть учётная запись?</span>
                    <span class="sugg-link to-sign">Войдите!</span>
                </div>
            </div>
        </template>
    </b-card>
</template>

<script lang="ts">
    import {Vue,Component,Prop} from 'vue-property-decorator'
    import Axios from 'axios'
    import {emailRegExp, FRONT_VALIDATION,COMMON_ERROR} from "./FormConst";


    const OK : string = "ok";
    const REGISTER_ROUTE: string = "/register";


    @Component
    export default class RegisterForm extends Vue {
        private http = Axios;

        private user_name = "";
        private login: string = "";
        private pass: string = "";
        private passConfirm: string  = "";


        public nameValid = true;
        public loginValid = true;
        public passValid = true;
        public passConfirmValid = true;

        public err = false;
        public isLoading = 0; //0 - not loading; 1 - loading; 2 - complete

        get nameState(){
            return this.nameValid;
        }
        get loginState(){
            return this.loginValid;
        }
        get passState(){
            return this.passValid;
        }
        get passConfirmState(){
            return this.passConfirmValid;
        }

        get invalidPass(){
            if(this.passValid){
                return "";
            }else {
                return FRONT_VALIDATION.password
            }
        }
        get invalidEmail(){
            if(this.loginValid){
                return ""
            }else{
                return FRONT_VALIDATION.email
            }
        }
        get invalidName(){
            if(this.nameValid){
                return "";
            }else{
                return FRONT_VALIDATION.name;
            }
        }
        get passConfirmed(){
             if (!this.passConfirmValid){
                 return FRONT_VALIDATION.passNotConfirmed;
             }else{
                 return "";
             }
        }

        canSubmit(){
            this.validateLogin();
            this.validatePass();
            this.validateName();
            this.confirmPass();

            return this.loginValid && this.passValid && this.nameValid && this.passConfirmValid;
        }
        validateName(){
            this.nameValid = (this.user_name != "") ? true : false;
        }
        validateLogin(){
            this.loginValid = (this.login.match(emailRegExp) && this.login != "" ? true : false)
        }
        validatePass(){
            this.passValid = (this.pass.length >= 6 ? true : false)
        }
        confirmPass(){
            this.passConfirmValid = (this.pass == this.passConfirm) ? true : false;
        }
        handleErrorResponse(data){
            let errBag = <HTMLParagraphElement>this.$refs.errBag;
            errBag.innerText = data.common;
            this.err = true;
            setTimeout(()=>{this.err = false; },3000);
        }

        register(): void {
            let credentials = {
                name: this.user_name,
                email: this.login,
                password: this.pass,
                remember: true
            };
            if (this.canSubmit()) {

                let data : any = null;
                let code : number = 0;

                this.isLoading = 1;

                this.http.post(REGISTER_ROUTE, credentials).then(response => {

                    data = response.data;
                    code = response.status;
                    this.isLoading = 2;

                    if(code == 200){
                        location.reload();
                    }
                }).catch(err => {
                    data = err.response.data;
                    code = err.response.status;
                    this.isLoading = 0;
                    if (code == 422) {
                        let err  = {common: data.name || data.email || data.password };
                        this.handleErrorResponse(err);
                    } else {
                        this.handleErrorResponse(COMMON_ERROR);
                    }
                });
            }
        }

    }

</script>

<style scoped lang="scss">
    .form-policies{
        position:relative;
        left: 30px;
    }
    .forgot-pass{
        font-size: 0.75em;
        cursor: pointer;
        margin: 11px 3px 8px 12px;
        font-weight: bold;
        color: #000;
        font-family: GloberBoldFree;
    }
    .buttons-line{
        text-align: center;
        margin: 15px 12%;
        width: 80%;
        *{
            display:inline-block;
        }
        @media all and (max-width: 480px) {
            .btn-group {
                margin: 10px;
            }
        }
    }
    .errBag{
        color: #de6266;
        font-size: 0.9em;
        margin: -10px 0 10px;
        text-align: center;
    }
    .button{
        color: #fff !important;
        cursor:pointer;
    }
    .soc-icon {
        display: inline-block;
        vertical-align: middle;
        width: 36px;
        height: 36px;
        border-radius: 3px;
        text-align: center;
        float: right;
        margin-left: 9px;
        background-color: #4c8103;
    }
    .soc-fb {
        &:hover {
            background: #476bb8;
        }
        padding: 5px;
        background-color: #3b5998;
    }
    .soc-vk {
        &:hover {
            background: #698ab0;
        }
        padding-top: 5px;
        background-color: #507299;
    }
    .soc-google {
        &:hover {
            background: #e7877e;
        }
        padding-top: 5px;
        background-color: #de5f54;
    }
    .soc-icon.soc-disabled {
        background-color: #6c6c6c;
        padding: 5px;
        &:hover {
            background-color: #999999;
            color: #6c6c6c;
        }
    }
    a {
        color: #fff;
        .fa-facebook {
            font-size: 24px;
            margin-top: 5px;
        }
        .fa-vk {
            font-size: 22px;
        }
        .fa-google {
            font-size: 22px;
            margin-top: 2px;
        }
    }
    .facebook {
        background: url(/public/assets/img/soc-icons/soc-sprite.png) 0px 0px no-repeat;
        width: 18px;
        height: 18px;
        display: inline-block;
        margin: 4px 0 0 0;
    }
    .vkontakte {
        background: url(/public/assets/img/soc-icons/soc-sprite.png) -43px -2px no-repeat;
        width: 23px;
        height: 14px;
        display: inline-block;
        margin: 6px 0 0 0;
    }
    .google {
        background: url(/public/assets/img/soc-icons/soc-sprite.png) -87px -1px no-repeat;
        width: 24px;
        height: 16px;
        display: inline-block;
        margin: 5px 0 0 0;
    }
    .loader{
        height:36px;
        width:36px;
        margin: -11px 26.125px;
        float:none;
    }
</style>
<style>
    @media all and (max-width:768px) {
        .sign-up {
            top: 8% !important;
        }
    }
</style>
