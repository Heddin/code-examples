<template>
    <b-card>
        <template slot="header">
            <h3 class="form-header-text">ВОССТАНОВЛЕНИЕ ПАРОЛЯ</h3>
            <div class="form-info-text">Введите email-адресс и мы вышлем ссылку на востановление пароля</div>
        </template>
        <transition name="fade">
            <p class="errBag" v-show="err" ref="errBag"></p>
        </transition>
        <b-form-group horizontal
                      :label-cols="2"
                      breakpoint="md"
                      description="Email, который используеться для входа на сайт"
                      label="Email"
                      label-class=""
                      label-for="mailField"
                      :invalid-feedback="invalidEmailFeedback"
                      :state="isEmailValid"
        >
            <b-form-input id="mailField"
                          type="email"
                          placeholder="example@mail.com"
                          title="Ваш email, который вы использовали для регистрации."
                          autocomplete="email"
                          v-model.trim="email"
                          @change="validateEmail"
            ></b-form-input>
        </b-form-group>
        <div class="buttons-line margin-0-25">
            <a  v-show="isLoading  == 0" class="button button-limeade-approx" @click="sendResetRequest">Выслать пароль</a>
            <img v-show="isLoading == 1" src="/assets/img/loaders/green.gif" class="loader" ref="loginLoader">
            <img v-show="isLoading == 2" src="/assets/img/loaders/success.png" class="loader" ref="loginLoader">
        </div>
        <template slot="footer">
            <div class="text-center">
                <div class="sugg">
                    <span>Нет учётной записи?</span>
                    <span class="sugg-link to-reg">Зарегистрируйтесь!</span>
                </div>
            </div>
        </template>
    </b-card>
</template>

<script lang="ts">
    import {Vue,Component} from 'vue-property-decorator'

    import Axios from 'axios'
    import {emailRegExp, FRONT_VALIDATION, COMMON_ERROR} from "./FormConst";

    const RESET_PASS_ROUTE : string = "/password/email";

    @Component
    export default class PassResetForm extends Vue {

        private http = Axios;
        private email: string = "";

        public emailValid: boolean = true;
        public err: boolean = false;

        public isLoading: number  = 0; //0 - not loading; 1 - loading; 2 - complete

        get isEmailValid(){
            return this.emailValid;
        }

        get invalidEmailFeedback(){
            if(this.isEmailValid){
                return "";
            }else{
                return FRONT_VALIDATION.email;
            }
        }

        canSubmit(){
            this.validateEmail();

            return this.emailValid;
        }

        validateEmail(){
            this.emailValid = (this.email.match(emailRegExp) || this.email != "") ? true : false;
        }
        handleErrorResponse(data){
            let errBag = <HTMLParagraphElement>this.$refs.errBag;

            this.err = true;
            
            if(data.common) {
                errBag.innerText = data.common;
                setTimeout(()=>{this.err = false; },3000);
            }else if(data.success){
                errBag.classList.add('_success');
                errBag.innerText = data.success;
                setTimeout(()=>{
                    this.err = false;
                    errBag.classList.remove('_success');

                    let overlay = <HTMLDivElement>document.getElementById('overlay');
                        overlay.click();
                        this.isLoading = 0;
                },5000);
            }
           
          
        }
        sendResetRequest(){
            if(this.canSubmit()){

                let data = null;
                let code = 0;

                this.isLoading = 1;

                this.http.post(RESET_PASS_ROUTE,{email:this.email}).then( response =>{

                    data = response.data;
                    code = response.status;

                    if(code == 200){
                       if(data.status == 'success') {
                           this.handleErrorResponse({success: data.message});
                           this.isLoading = 2;
                       }else{
                           this.handleErrorResponse({common: data.message})
                           this.isLoading = 0;
                       }
                    }
                }).catch( err => {
                    if(err.response) {
                        data = err.response.data;  this.handleErrorResponse({common:data.email});
                        this.isLoading = 0;
                    }
                });
            }
        }
    }
</script>

<style scoped lang="scss">
    .margin-0-25{
        margin: 0 25%;
    }
    .errBag{
        color: #de6266;
        font-size: 0.9em;
        margin: -10px 0 10px;
        text-align: center;
    }
    ._success{
        @extend .errBag;
        color: #5fa204 !important;
    }
    .loader{
        height:36px;
        width:36px;
        display: block;
        float:none;
        margin: 0 37%;
    }
</style>