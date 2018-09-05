import ManageCompany from './components/manage-company.vue';
import ManageCourses from './components/manage-courses.vue';
import ManageUsers from './components/manage-users.vue';
import ManageTasks from './components/manage-tasks.vue';
import ManageStats from './components/manage-stats.vue';

let routes = [];


export const API = {
    _base : "/api/company/",

    /*@param id - company ID*/
    getCompany : id =>{
      return API._base + 'get/' + id;
    },
    updateCompany : id =>{
      return API._base + 'edit/' + id;
    },
    deleteCompany : id =>{
      return API._base + 'remove/' + id;
    },
    sendInvite : () => {
        return API._base + 'invite'
    },
    changeStatus : () => {
        return API._base + 'change_status'
    },
    removeMember : ()=>{
        return API._base + 'remove_member'
    },
    cancelInvite : ()=>{
        return API._base + 'cancel_invite'
    },
    reSendInvite: ()=>{
        return API._base + 'resend_invite'
    },
    assignTask: ()=>{
        return API._base + 'assign-task'
    },
};

export default routes = [
    {
      path: '/',
      name: 'ManageCompany',
      component: ManageCompany,
    },
    {
        path: '/manage-users',
        name: 'ManageUsers',
        component: ManageUsers,
    },
    {
        path: '/manage-courses',
        name: 'ManageCourses',
        component: ManageCourses,
    },
    {
        path: '/manage-tasks',
        name: 'ManageTasks',
        component: ManageTasks,
        props: {needs_activate:true}
    },
    {
        path: '/manage-stats',
        name: 'ManageStats',
        component: ManageStats,
    },
    {
        path: '*',
        redirect: '/'
    },
];
