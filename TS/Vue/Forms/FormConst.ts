export const ERROR_CLASS: string = "has-error";
export const SUCCESS_CLASS: string = "has-success";
export const INVALID_FIELD_CLASS: string = "form-err-msg";
export const INVALID_TEXTAREA_CLASS: string = "form-err-msg-text_area";
export const INVALID_FIELD_CLASS_COMMON: string = INVALID_FIELD_CLASS + "-common";
export const COMMON_ERROR: object = {
    common: "Неизвесная ошибка. Попробуйте позже"
};
export const FRONT_VALIDATION = {
    email: "Возможно вы ошиблись в email-адресе.",
    name: "Имя не может быть пустым.",
    password: "Пароль не может быть меньше 6-ти символов",
    passNotConfirmed: "Введенные пароли не совпадают",
    passwordIsEmpty:"Пожалуйста, введите пароль",
    msgText: "Пожалуйста, введите вопрос.",
    msgSent: "Вопрос успешо отправлен",
    inviteMsgTxt: "Пожалуйста, введите сообщение.",
    allEmpty: "Пожалуйста, заполните все поля."
};
export const emailRegExp : RegExp = new RegExp("(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\\])");
