<!--
  - Copyright (c) Enalean, 2019 - present. All Rights Reserved.
  -
  - This file is a part of Tuleap.
  -
  - Tuleap is free software; you can redistribute it and/or modify
  - it under the terms of the GNU General Public License as published by
  - the Free Software Foundation; either version 2 of the License, or
  - (at your option) any later version.
  -
  - Tuleap is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU General Public License for more details.
  -
  - You should have received a copy of the GNU General Public License
  - along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
  -
  -->
<template>
    <div class="empty-page">
        <div class="empty-page-illustration">
            <board-without-any-columns-for-admin-svg/>
        </div>
        <div class="empty-page-text-with-small-text">
            <translate>This taskboard is not properly configured</translate>
            <div class="empty-page-small-text">
                <translate>You have to configure the columns mapping</translate>
            </div>
        </div>
        <a class="tlp-button-primary tlp-button-large" v-bind:href="admin_url">
            <i class="fa fa-long-arrow-right tlp-button-icon"></i>
            <translate>Define columns</translate>
        </a>
    </div>
</template>
<script lang="ts">
import Vue from "vue";
import { Component, Prop } from "vue-property-decorator";
import BoardWithoutAnyColumnsForAdminSvg from "./BoardWithoutAnyColumnsForAdminSvg.vue";

@Component({
    components: { BoardWithoutAnyColumnsForAdminSvg }
})
export default class BoardWithoutAnyColumnsErrorForAdmin extends Vue {
    @Prop()
    readonly admin_url!: string;
}
</script>
