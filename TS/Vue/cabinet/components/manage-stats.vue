<template>
    <b-container class="padded">
        <div class="sort">
            <span class="sortBy">Сортировать по:</span>
            <input id="toggle" type="checkbox" v-model="toggle">
            <label class="switch" for="toggle"></label>
        </div>

        <keep-alive>
            <component v-bind:is="currentTabComponent"></component>
        </keep-alive>
    </b-container>
</template>

<script>
	import ByUsers from './stats/ByUsers.vue';
	import ByCourses from './stats/ByCourses.vue';

    export default {
        name: 'ManageStats',
        data: function()
        {
        	return {
		        toggle: true
            }
        },
        computed:
        {
	        currentTabComponent: function()
            {
            	return !this.toggle ? 'ByUsers' : ByCourses;
            }
        },
        components: { ByUsers, ByCourses }
    }
</script>

<style scoped lang="scss">
    .padded{
        padding: 15px;
    }
</style>
