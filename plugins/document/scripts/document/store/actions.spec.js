/*
 * Copyright (c) Enalean, 2018-Present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

import { mockFetchError } from "tlp-mocks";
import {
    restore as restoreUploadFile,
    restore as restoreUploadVersion,
    rewire$uploadFile,
    rewire$uploadVersion
} from "./actions-helpers/upload-file.js";
import {
    restore as restoreRestQuerier,
    rewire$addNewEmpty,
    rewire$addNewFile,
    rewire$addUserLegacyUIPreferency,
    rewire$cancelUpload,
    rewire$createNewVersion,
    rewire$deleteEmbeddedFile,
    rewire$deleteEmptyDocument,
    rewire$deleteFile,
    rewire$deleteFolder,
    rewire$deleteLink,
    rewire$deleteLockEmbedded,
    rewire$deleteLockFile,
    rewire$deleteUserPreferenciesForFolderInProject,
    rewire$deleteUserPreferenciesForUnderConstructionModal,
    rewire$deleteWiki,
    rewire$getDocumentManagerServiceInformation,
    rewire$getItem,
    rewire$getItemsReferencingSameWikiPage,
    rewire$getParents,
    rewire$patchUserPreferenciesForFolderInProject,
    rewire$postEmbeddedFile,
    rewire$postLinkVersion,
    rewire$postLockEmbedded,
    rewire$postLockFile,
    rewire$postNewLinkVersionFromEmpty,
    rewire$postNewEmbeddedFileVersionFromEmpty,
    rewire$postWiki,
    rewire$putEmbeddedFileMetadata,
    rewire$putEmbeddedFilePermissions,
    rewire$putEmptyDocumentMetadata,
    rewire$putEmptyDocumentPermissions,
    rewire$putFileMetadata,
    rewire$putFilePermissions,
    rewire$putFolderPermissions,
    rewire$putLinkMetadata,
    rewire$putLinkPermissions,
    rewire$putWikiMetadata,
    rewire$putWikiPermissions,
    rewire$removeUserPreferenceForEmbeddedDisplay,
    rewire$setNarrowModeForEmbeddedDisplay
} from "../api/rest-querier.js";
import {
    restore as restoreLoadFolderContent,
    rewire$loadFolderContent
} from "./actions-helpers/load-folder-content.js";
import {
    restore as restoreLoadAscendantHierarchy,
    rewire$loadAscendantHierarchy
} from "./actions-helpers/load-ascendant-hierarchy.js";
import {
    restore as restoreHandleErrors,
    rewire$handleErrorsForModal
} from "./actions-helpers/handle-errors.js";
import {
    restore as restoreUGroupsHelper,
    rewire$getProjectUserGroupsWithoutServiceSpecialUGroups
} from "../helpers/permissions/ugroups.js";

import {
    TYPE_EMBEDDED,
    TYPE_EMPTY,
    TYPE_FILE,
    TYPE_FOLDER,
    TYPE_LINK,
    TYPE_WIKI
} from "../constants.js";
import {
    addNewUploadFile,
    cancelFileUpload,
    cancelFolderUpload,
    cancelVersionUpload,
    createNewEmbeddedFileVersionFromModal,
    createNewFileVersion,
    createNewFileVersionFromModal,
    createNewItem,
    createNewLinkVersionFromModal,
    createNewVersionFromEmpty,
    createNewWikiVersionFromModal,
    deleteItem,
    displayEmbeddedInLargeMode,
    displayEmbeddedInNarrowMode,
    getWikisReferencingSameWikiPage,
    loadDocumentWithAscendentHierarchy,
    loadFolder,
    loadProjectUserGroupsIfNeeded,
    loadRootFolder,
    lockDocument,
    setUserPreferenciesForFolder,
    setUserPreferenciesForUI,
    toggleQuickLook,
    unlockDocument,
    unsetUnderConstructionUserPreference,
    updateFolderMetadata,
    updateMetadata,
    updatePermissions
} from "./actions.js";

describe("Store actions", () => {
    afterEach(() => {
        restoreRestQuerier();
        restoreLoadFolderContent();
        restoreLoadAscendantHierarchy();
        restoreUploadFile();
        restoreUploadVersion();
    });

    let context,
        getDocumentManagerServiceInformation,
        getItem,
        loadFolderContent,
        loadAscendantHierarchy,
        deleteUserPreferenciesForFolderInProject,
        deleteUserPreferenciesForUnderConstructionModal,
        patchUserPreferenciesForFolderInProject,
        addUserLegacyUIPreferency,
        addNewEmpty,
        addNewFile,
        uploadFile,
        cancelUpload,
        createNewVersion,
        uploadVersion,
        postEmbeddedFile,
        postWiki,
        postLinkVersion;

    beforeEach(() => {
        const project_id = 101;
        context = {
            commit: jasmine.createSpy("commit"),
            state: {
                project_id,
                current_folder_ascendant_hierarchy: []
            }
        };

        getDocumentManagerServiceInformation = jasmine.createSpy(
            "getDocumentManagerServiceInformation"
        );
        rewire$getDocumentManagerServiceInformation(getDocumentManagerServiceInformation);

        getItem = jasmine.createSpy("getItem");
        rewire$getItem(getItem);

        loadFolderContent = jasmine.createSpy("loadFolderContent");
        rewire$loadFolderContent(loadFolderContent);

        loadAscendantHierarchy = jasmine.createSpy("loadAscendantHierarchy");
        rewire$loadAscendantHierarchy(loadAscendantHierarchy);

        addNewEmpty = jasmine.createSpy("addNewEmpty");
        rewire$addNewEmpty(addNewEmpty);

        addNewFile = jasmine.createSpy("addNewFile");
        rewire$addNewFile(addNewFile);

        uploadFile = jasmine.createSpy("uploadFile");
        rewire$uploadFile(uploadFile);

        uploadVersion = jasmine.createSpy("uploadVersion");
        rewire$uploadVersion(uploadVersion);

        cancelUpload = jasmine.createSpy("cancelUpload");
        rewire$cancelUpload(cancelUpload);

        createNewVersion = jasmine.createSpy("createNewVersion");
        rewire$createNewVersion(createNewVersion);

        deleteUserPreferenciesForFolderInProject = jasmine.createSpy(
            "deleteUserPreferenciesForFolderInProject"
        );
        rewire$deleteUserPreferenciesForFolderInProject(deleteUserPreferenciesForFolderInProject);

        deleteUserPreferenciesForUnderConstructionModal = jasmine.createSpy(
            "deleteUserPreferenciesForUnderConstructionModal"
        );
        rewire$deleteUserPreferenciesForUnderConstructionModal(
            deleteUserPreferenciesForUnderConstructionModal
        );

        patchUserPreferenciesForFolderInProject = jasmine.createSpy(
            "patchUserPreferenciesForFolderInProject"
        );
        rewire$patchUserPreferenciesForFolderInProject(patchUserPreferenciesForFolderInProject);

        postEmbeddedFile = jasmine.createSpy("postEmbeddedFile");
        rewire$postEmbeddedFile(postEmbeddedFile);

        addUserLegacyUIPreferency = jasmine.createSpy("addUserLegacyUIPreferency");
        rewire$addUserLegacyUIPreferency(addUserLegacyUIPreferency);

        postWiki = jasmine.createSpy("postWiki");
        rewire$postWiki(postWiki);

        postLinkVersion = jasmine.createSpy("postLinkVersion");
        rewire$postLinkVersion(postLinkVersion);
    });

    afterEach(() => {
        restoreUGroupsHelper();
    });

    describe("loadRootFolder()", () => {
        it("load document root and then load its own content", async () => {
            const root_item = {
                id: 3,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            const service = {
                root_item
            };

            getDocumentManagerServiceInformation.and.returnValue(service);

            await loadRootFolder(context);

            expect(context.commit).toHaveBeenCalledWith("beginLoading");
            expect(context.commit).toHaveBeenCalledWith("setCurrentFolder", root_item);
            expect(context.commit).toHaveBeenCalledWith("stopLoading");
            expect(loadFolderContent).toHaveBeenCalled();
            await expectAsync(loadFolderContent.calls.mostRecent().args[2]).toBeResolvedTo(
                root_item
            );
        });

        it("When the user does not have access to the project, an error will be raised", async () => {
            mockFetchError(getDocumentManagerServiceInformation, {
                status: 403,
                error_json: {
                    error: {
                        message: "User can't access project"
                    }
                }
            });

            await loadRootFolder(context);

            expect(context.commit).toHaveBeenCalledWith("error/switchFolderPermissionError");
            expect(context.commit).toHaveBeenCalledWith("stopLoading");
        });

        it("When the project can't be found, an error will be raised", async () => {
            const error_message = "Project does not exist.";
            mockFetchError(getDocumentManagerServiceInformation, {
                status: 404,
                error_json: {
                    error: {
                        message: error_message
                    }
                }
            });

            await loadRootFolder(context);

            expect(context.commit).toHaveBeenCalledWith(
                "error/setFolderLoadingError",
                error_message
            );
            expect(context.commit).toHaveBeenCalledWith("stopLoading");
        });

        it("When an error occurred, then the translated exception will be raised", async () => {
            const error_message = "My translated exception";
            mockFetchError(getDocumentManagerServiceInformation, {
                status: 404,
                error_json: {
                    error: {
                        i18n_error_message: error_message
                    }
                }
            });

            await loadRootFolder(context);

            expect(context.commit).toHaveBeenCalledWith(
                "error/setFolderLoadingError",
                error_message
            );
            expect(context.commit).toHaveBeenCalledWith("stopLoading");
        });
    });

    describe("loadFolder", () => {
        it("loads ascendant hierarchy and content for stored current folder", async () => {
            const current_folder = {
                id: 3,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            context.state.current_folder = current_folder;

            await loadFolder(context, 3);

            expect(getItem).not.toHaveBeenCalled();
            expect(loadFolderContent).toHaveBeenCalled();
            expect(loadAscendantHierarchy).toHaveBeenCalled();
            await expectAsync(loadFolderContent.calls.mostRecent().args[2]).toBeResolvedTo(
                current_folder
            );
            await expectAsync(loadAscendantHierarchy.calls.mostRecent().args[2]).toBeResolvedTo(
                current_folder
            );
        });

        it("gets item if there isn't any current folder in the store", async () => {
            const folder_to_fetch = {
                id: 3,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            getItem.and.returnValue(Promise.resolve(folder_to_fetch));

            await loadFolder(context, 3);

            expect(getItem).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("setCurrentFolder", folder_to_fetch);
            expect(loadFolderContent).toHaveBeenCalled();
            expect(loadAscendantHierarchy).toHaveBeenCalled();
            await expectAsync(loadFolderContent.calls.mostRecent().args[2]).toBeResolvedTo(
                folder_to_fetch
            );
            await expectAsync(loadAscendantHierarchy.calls.mostRecent().args[2]).toBeResolvedTo(
                folder_to_fetch
            );
        });

        it("gets item when the requested folder is not in the store", async () => {
            context.state.current_folder = {
                id: 1,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            const folder_to_fetch = {
                id: 3,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            getItem.and.returnValue(Promise.resolve(folder_to_fetch));

            await loadFolder(context, 3);

            expect(getItem).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("setCurrentFolder", folder_to_fetch);
            expect(loadFolderContent).toHaveBeenCalled();
            expect(loadAscendantHierarchy).toHaveBeenCalled();
            await expectAsync(loadFolderContent.calls.mostRecent().args[2]).toBeResolvedTo(
                folder_to_fetch
            );
            await expectAsync(loadAscendantHierarchy.calls.mostRecent().args[2]).toBeResolvedTo(
                folder_to_fetch
            );
        });

        it("does not load ascendant hierarchy if folder is already inside the current one", async () => {
            const folder_a = {
                id: 2,
                title: "folder A",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };
            const folder_b = {
                id: 3,
                title: "folder B",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };

            context.state.current_folder_ascendant_hierarchy = [folder_a, folder_b];
            context.state.current_folder = folder_a;

            await loadFolder(context, 2);

            expect(loadAscendantHierarchy).not.toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("saveAscendantHierarchy", [folder_a]);
            expect(context.commit).not.toHaveBeenCalledWith("setCurrentFolder");
        });

        it("overrides the stored current folder with the one found in ascendant hierarchy", async () => {
            const folder_a = {
                id: 2,
                title: "folder A",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };
            const folder_b = {
                id: 3,
                title: "folder B",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };

            context.state.current_folder_ascendant_hierarchy = [folder_a, folder_b];
            context.state.current_folder = folder_b;

            await loadFolder(context, 2);

            expect(loadAscendantHierarchy).not.toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("saveAscendantHierarchy", [folder_a]);
            expect(context.commit).toHaveBeenCalledWith("setCurrentFolder", folder_a);
        });

        it("does not override the stored current folder with the one found in ascendant hierarchy if they are the same", async () => {
            const folder_a = {
                id: 2,
                title: "folder A",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };
            const folder_b = {
                id: 3,
                title: "folder B",
                owner: {
                    id: 101
                },
                last_update_date: "2018-08-07T16:42:49+02:00"
            };

            context.state.current_folder_ascendant_hierarchy = [folder_a, folder_b];
            context.state.current_folder = folder_a;

            await loadFolder(context, 2);

            expect(loadAscendantHierarchy).not.toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("saveAscendantHierarchy", [folder_a]);
            expect(context.commit).not.toHaveBeenCalledWith("setCurrentFolder", folder_a);
        });
    });

    describe("setUserPreferenciesForFolder", () => {
        it("sets the user preference for the state of a given folder if its new state is 'open' (expanded)", async () => {
            const folder_id = 30;
            const should_be_closed = false;
            const context = {
                state: {
                    user_id: 102,
                    project_id: 110
                }
            };

            await setUserPreferenciesForFolder(context, [folder_id, should_be_closed]);

            expect(patchUserPreferenciesForFolderInProject).toHaveBeenCalled();
            expect(deleteUserPreferenciesForFolderInProject).not.toHaveBeenCalled();
        });

        it("deletes the user preference for the state of a given folder if its new state is 'closed' (collapsed)", async () => {
            const folder_id = 30;
            const should_be_closed = true;
            const context = {
                state: {
                    user_id: 102,
                    project_id: 110
                }
            };

            await setUserPreferenciesForFolder(context, [folder_id, should_be_closed]);

            expect(patchUserPreferenciesForFolderInProject).not.toHaveBeenCalled();
            expect(deleteUserPreferenciesForFolderInProject).toHaveBeenCalled();
        });
    });

    describe("setUserPreferenciesForUI", () => {
        it("sets the user preference to old ui", async () => {
            const context = {
                state: {
                    user_id: 102,
                    project_id: 110
                }
            };

            await setUserPreferenciesForUI(context);

            expect(addUserLegacyUIPreferency).toHaveBeenCalled();
        });
    });

    describe("unsetUnderConstructionUserPreference", () => {
        it("unset the under construction preference", async () => {
            const context = {
                commit: jasmine.createSpy("commit"),
                state: {
                    user_id: 102,
                    project_id: 110
                }
            };

            await unsetUnderConstructionUserPreference(context);

            expect(deleteUserPreferenciesForUnderConstructionModal).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("removeIsUnderConstruction");
        });
    });

    describe("createNewItem", () => {
        it("Creates new document and reload folder content", async () => {
            const created_item_reference = { id: 66 };
            addNewEmpty.and.returnValue(Promise.resolve(created_item_reference));

            const item = { id: 66, title: "whatever", type: "empty" };
            const parent = { id: 2, title: "my folder", type: "folder", is_expanded: true };
            const current_folder = parent;
            getItem.and.returnValue(Promise.resolve(item));

            await createNewItem(context, [item, parent, current_folder]);

            expect(getItem).toHaveBeenCalledWith(66);
            expect(context.commit).toHaveBeenCalledWith("addJustCreatedItemToFolderContent", item);
            expect(context.commit).not.toHaveBeenCalledWith("error/setModalError");
        });

        it("Stores error when document creation fail", async () => {
            const error_message = "`title` is required.";
            mockFetchError(addNewEmpty, {
                status: 400,
                error_json: {
                    error: {
                        message: error_message
                    }
                }
            });
            const parent = { id: 2, title: "my folder", type: "folder", is_expanded: true };
            const current_folder = parent;
            const item = { id: 66, title: "", type: "empty" };

            await createNewItem(context, [item, parent, current_folder]);

            expect(context.commit).not.toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith("error/setModalError", error_message);
        });

        it("displays the created item when it is created in the current folder", async () => {
            const created_item_reference = { id: 66 };
            addNewEmpty.and.returnValue(Promise.resolve(created_item_reference));

            const item = { id: 66, title: "whatever", type: "empty" };
            getItem.and.returnValue(Promise.resolve(item));

            const folder_of_created_item = { id: 10 };
            const current_folder = { id: 10 };

            await createNewItem(context, [item, folder_of_created_item, current_folder]);

            expect(context.commit).not.toHaveBeenCalledWith("addDocumentToFoldedFolder");
            expect(context.commit).toHaveBeenCalledWith("addJustCreatedItemToFolderContent", item);
        });
        it("not displays the created item when it is created in a collapsed folder", async () => {
            const created_item_reference = { id: 66 };
            addNewEmpty.and.returnValue(Promise.resolve(created_item_reference));

            const item = { id: 66, title: "whatever", type: "empty" };
            getItem.and.returnValue(Promise.resolve(item));

            const current_folder = { id: 30 };
            const collapsed_folder_of_created_item = { id: 10, parent_id: 30, is_expanded: false };

            await createNewItem(context, [item, collapsed_folder_of_created_item, current_folder]);
            expect(context.commit).toHaveBeenCalledWith("addDocumentToFoldedFolder", [
                collapsed_folder_of_created_item,
                item,
                false
            ]);
            expect(context.commit).toHaveBeenCalledWith("addJustCreatedItemToFolderContent", item);
        });
        it("displays the created item when it is created in a expanded folder which is not the same as the current folder", async () => {
            const created_item_reference = { id: 66 };
            addNewEmpty.and.returnValue(Promise.resolve(created_item_reference));

            const item = { id: 66, title: "whatever", type: "empty" };
            getItem.and.returnValue(Promise.resolve(item));

            const current_folder = { id: 18 };
            const collapsed_folder_of_created_item = { id: 10, parent_id: 30, is_expanded: true };

            await createNewItem(context, [item, collapsed_folder_of_created_item, current_folder]);
            expect(context.commit).not.toHaveBeenCalledWith("addDocumentToFoldedFolder");
            expect(context.commit).toHaveBeenCalledWith("addJustCreatedItemToFolderContent", item);
        });
        it("displays the created file when it is created in the current folder", async () => {
            context.state.folder_content = [{ id: 10 }];
            const created_item_reference = { id: 66 };

            addNewFile.and.returnValue(Promise.resolve(created_item_reference));
            const file_name_properties = { name: "filename.txt", size: 10, type: "text/plain" };
            const item = {
                id: 66,
                title: "filename.txt",
                description: "",
                type: TYPE_FILE,
                file_properties: { file: file_name_properties }
            };

            getItem.and.returnValue(Promise.resolve(item));
            const folder_of_created_item = { id: 10 };
            const current_folder = { id: 10 };
            const uploader = {};
            uploadFile.and.returnValue(uploader);

            const expected_fake_item_with_uploader = {
                id: 66,
                title: "filename.txt",
                parent_id: 10,
                type: TYPE_FILE,
                file_type: "text/plain",
                is_uploading: true,
                progress: 0,
                uploader,
                upload_error: null
            };

            await createNewItem(context, [item, folder_of_created_item, current_folder]);

            expect(uploadFile).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                expected_fake_item_with_uploader
            );
            expect(context.commit).toHaveBeenCalledWith("addDocumentToFoldedFolder", [
                folder_of_created_item,
                expected_fake_item_with_uploader,
                true
            ]);
            expect(context.commit).toHaveBeenCalledWith(
                "addFileInUploadsList",
                expected_fake_item_with_uploader
            );
        });
        it("not displays the created file when it is created in a collapsed folder and displays the progress bar along the folder", async () => {
            context.state.folder_content = [{ id: 10 }];
            const created_item_reference = { id: 66 };

            addNewFile.and.returnValue(Promise.resolve(created_item_reference));
            const file_name_properties = { name: "filename.txt", size: 10, type: "text/plain" };
            const item = {
                id: 66,
                title: "filename.txt",
                description: "",
                type: TYPE_FILE,
                file_properties: { file: file_name_properties }
            };

            getItem.and.returnValue(Promise.resolve(item));
            const current_folder = { id: 30 };
            const collapsed_folder_of_created_item = { id: 10, parent_id: 30, is_expanded: false };
            const uploader = {};
            uploadFile.and.returnValue(uploader);

            const expected_fake_item_with_uploader = {
                id: 66,
                title: "filename.txt",
                parent_id: 10,
                type: TYPE_FILE,
                file_type: "text/plain",
                is_uploading: true,
                progress: 0,
                uploader,
                upload_error: null
            };

            await createNewItem(context, [item, collapsed_folder_of_created_item, current_folder]);

            expect(uploadFile).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                expected_fake_item_with_uploader
            );
            expect(context.commit).toHaveBeenCalledWith("addDocumentToFoldedFolder", [
                collapsed_folder_of_created_item,
                expected_fake_item_with_uploader,
                false
            ]);
            expect(context.commit).toHaveBeenCalledWith(
                "addFileInUploadsList",
                expected_fake_item_with_uploader
            );
            expect(context.commit).toHaveBeenCalledWith(
                "toggleCollapsedFolderHasUploadingContent",
                [collapsed_folder_of_created_item, true]
            );
        });
        it("displays the created file when it is created in a extanded sub folder and not displays the progress bar along the folder", async () => {
            context.state.folder_content = [{ id: 10 }];
            const created_item_reference = { id: 66 };

            addNewFile.and.returnValue(Promise.resolve(created_item_reference));
            const file_name_properties = { name: "filename.txt", size: 10, type: "text/plain" };
            const item = {
                id: 66,
                title: "filename.txt",
                description: "",
                type: TYPE_FILE,
                file_properties: { file: file_name_properties }
            };

            getItem.and.returnValue(Promise.resolve(item));
            const current_folder = { id: 30 };
            const extended_folder_of_created_item = { id: 10, parent_id: 30, is_expanded: true };
            const uploader = {};
            uploadFile.and.returnValue(uploader);

            const expected_fake_item_with_uploader = {
                id: 66,
                title: "filename.txt",
                parent_id: 10,
                type: TYPE_FILE,
                file_type: "text/plain",
                is_uploading: true,
                progress: 0,
                uploader,
                upload_error: null
            };

            await createNewItem(context, [item, extended_folder_of_created_item, current_folder]);

            expect(uploadFile).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                expected_fake_item_with_uploader
            );
            expect(context.commit).toHaveBeenCalledWith("addDocumentToFoldedFolder", [
                extended_folder_of_created_item,
                expected_fake_item_with_uploader,
                true
            ]);
            expect(context.commit).toHaveBeenCalledWith(
                "addFileInUploadsList",
                expected_fake_item_with_uploader
            );
            expect(context.commit).toHaveBeenCalledWith(
                "toggleCollapsedFolderHasUploadingContent",
                [extended_folder_of_created_item, false]
            );
        });
    });

    describe("addNewUploadFile", () => {
        it("Creates a fake item with created item reference", async () => {
            context.state.folder_content = [{ id: 45 }];
            const dropped_file = { name: "filename.txt", size: 10, type: "text/plain" };
            const parent = { id: 42 };

            const created_item_reference = { id: 66 };
            addNewFile.and.returnValue(Promise.resolve(created_item_reference));
            const uploader = {};
            uploadFile.and.returnValue(uploader);

            await addNewUploadFile(context, [dropped_file, parent, "filename.txt", "", true]);

            const expected_fake_item_with_uploader = {
                id: 66,
                title: "filename.txt",
                parent_id: 42,
                type: TYPE_FILE,
                file_type: "text/plain",
                is_uploading: true,
                progress: 0,
                uploader,
                upload_error: null
            };
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                expected_fake_item_with_uploader
            );
        });
        it("Starts upload", async () => {
            context.state.folder_content = [{ id: 45 }];
            const dropped_file = { name: "filename.txt", size: 10, type: "text/plain" };
            const parent = { id: 42 };

            const created_item_reference = { id: 66 };
            addNewFile.and.returnValue(Promise.resolve(created_item_reference));
            const uploader = {};
            uploadFile.and.returnValue(uploader);

            await addNewUploadFile(context, [dropped_file, parent, "filename.txt", "", true]);

            const expected_fake_item = {
                id: 66,
                title: "filename.txt",
                parent_id: 42,
                type: TYPE_FILE,
                file_type: "text/plain",
                is_uploading: true,
                progress: 0,
                uploader,
                upload_error: null
            };
            expect(uploadFile).toHaveBeenCalledWith(
                context,
                dropped_file,
                expected_fake_item,
                created_item_reference,
                parent
            );
        });
        it("Does not start upload nor create fake item if item reference already exist in the store", async () => {
            context.state.folder_content = [{ id: 45 }, { id: 66 }];
            const dropped_file = { name: "filename.txt", size: 10, type: "text/plain" };
            const parent = { id: 42 };

            const created_item_reference = { id: 66 };
            addNewFile.and.returnValue(Promise.resolve(created_item_reference));

            await addNewUploadFile(context, [dropped_file, parent, "filename.txt", "", true]);

            expect(context.commit).not.toHaveBeenCalled();
            expect(uploadFile).not.toHaveBeenCalled();
        });
        it("does not start upload if file is empty", async () => {
            context.state.folder_content = [{ id: 45 }];
            const dropped_file = { name: "empty-file.txt", size: 0, type: "text/plain" };
            const parent = { id: 42 };

            const created_item_reference = { id: 66 };
            addNewFile.and.returnValue(Promise.resolve(created_item_reference));

            const created_item = { id: 66, parent_id: 42, type: "file" };
            getItem.and.returnValue(Promise.resolve(created_item));

            await addNewUploadFile(context, [dropped_file, parent, "filename.txt", "", true]);

            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                created_item
            );
            expect(uploadFile).not.toHaveBeenCalled();
        });
    });
    describe("cancelFileUpload", () => {
        let item;
        beforeEach(() => {
            item = {
                uploader: {
                    abort: jasmine.createSpy("abort")
                }
            };
        });

        it("asks to tus client to abort the upload", async () => {
            await cancelFileUpload(context, item);
            expect(item.uploader.abort).toHaveBeenCalled();
        });
        it("asks to tus server to abort the upload, because tus client does not do it for us", async () => {
            await cancelFileUpload(context, item);
            expect(cancelUpload).toHaveBeenCalledWith(item);
        });
        it("remove item from the store", async () => {
            await cancelFileUpload(context, item);
            expect(context.commit).toHaveBeenCalledWith("removeItemFromFolderContent", item);
        });
        it("remove item from the store even if there is an error on cancelUpload", async () => {
            cancelUpload.and.throwError("Failed to fetch");
            await cancelFileUpload(context, item);
            expect(context.commit).toHaveBeenCalledWith("removeItemFromFolderContent", item);
        });
    });

    describe("cancelVersionUpload", () => {
        let item;
        beforeEach(() => {
            item = {
                uploader: {
                    abort: jasmine.createSpy("abort")
                }
            };
        });

        it("asks to tus client to abort the upload", async () => {
            await cancelVersionUpload(context, item);
            expect(item.uploader.abort).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("removeVersionUploadProgress", item);
        });
    });
    describe("createNewFileVersion", () => {
        it("does not trigger any upload if the file is empty", async () => {
            const dropped_file = { name: "filename.txt", size: 0, type: "text/plain" };
            const item = {};

            createNewVersion.and.returnValue(Promise.resolve());

            await createNewFileVersion(context, [item, dropped_file]);

            expect(uploadVersion).not.toHaveBeenCalled();
        });
        it("upload a new version of file", async () => {
            const item = { id: 45, lock_info: null };
            context.state.folder_content = [{ id: 45 }];
            const dropped_file = { name: "filename.txt", size: 123, type: "text/plain" };

            const new_version = { upload_href: "/uploads/docman/version/42" };
            createNewVersion.and.returnValue(Promise.resolve(new_version));

            const uploader = {};
            uploadVersion.and.returnValue(uploader);

            await createNewFileVersion(context, [item, dropped_file]);

            expect(uploadVersion).toHaveBeenCalled();
        });
    });
    describe("createNewFileVersionFromModal", () => {
        it("uploads a new version of a file", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const updated_file = { name: "filename.txt", size: 123, type: "text/plain" };

            const new_version = { upload_href: "/uploads/docman/version/42" };
            createNewVersion.and.returnValue(Promise.resolve(new_version));

            const uploader = {};
            uploadVersion.and.returnValue(uploader);

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";
            const is_version_locked = true;

            await createNewFileVersionFromModal(context, [
                item,
                updated_file,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(createNewVersion).toHaveBeenCalled();
            expect(uploadVersion).toHaveBeenCalled();
        });
        it("throws an error when there is a problem with the version creation", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const update_fail = {};

            createNewVersion.and.throwError("An error occurred ");

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";

            await createNewFileVersionFromModal(context, [
                item,
                update_fail,
                version_title,
                version_changelog
            ]);

            expect(createNewVersion).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("error/setModalError", jasmine.anything());
            expect(uploadVersion).not.toHaveBeenCalled();
        });

        it("throws an error when there is a problem with the version creation", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const update_fail = {};

            mockFetchError(createNewVersion, {
                status: 400
            });

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";

            await createNewFileVersionFromModal(context, [
                item,
                update_fail,
                version_title,
                version_changelog
            ]);

            expect(createNewVersion).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "error/setModalError",
                "Internal server error"
            );
            expect(uploadVersion).not.toHaveBeenCalled();
        });
    });

    describe("createNewEmbeddedFileVersionFromModal", () => {
        it("updates an embedded file", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const new_html_content = { content: "<h1>Hello world!</h1>}}" };

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";
            const is_version_locked = true;

            await createNewEmbeddedFileVersionFromModal(context, [
                item,
                new_html_content,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postEmbeddedFile).toHaveBeenCalled();
        });
        it("throws an error when there is a problem with the update", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const new_html_content = { content: "<h1>Hello world!</h1>}}" };

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";
            const is_version_locked = true;

            postEmbeddedFile.and.throwError("nope");

            await createNewEmbeddedFileVersionFromModal(context, [
                item,
                new_html_content,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postEmbeddedFile).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("error/setModalError", jasmine.anything());
        });
    });

    describe("createNewWikiVersionFromModal", () => {
        it("updates a wiki page name", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const page_name = "kinky wiki";

            const version_title = "NSFW";
            const version_changelog = "Changed title to NSFW";
            const is_version_locked = true;

            await createNewWikiVersionFromModal(context, [
                item,
                page_name,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postWiki).toHaveBeenCalled();
        });
        it("throws an error when there is a problem with the update", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const page_name = "kinky wiki";

            const version_title = "NSFW";
            const version_changelog = "Changed title to NSFW";
            const is_version_locked = true;

            postWiki.and.throwError("nope");

            await createNewWikiVersionFromModal(context, [
                item,
                page_name,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postWiki).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("error/setModalError", jasmine.anything());
        });
    });

    describe("createNewLinkVersionFromModal", () => {
        it("updates a link url", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const new_link_url = "https://moogle.fr";

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";
            const is_version_locked = true;

            await createNewLinkVersionFromModal(context, [
                item,
                new_link_url,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postLinkVersion).toHaveBeenCalled();
        });
        it("throws an error when there is a problem with the update", async () => {
            const item = { id: 45 };
            context.state.folder_content = [{ id: 45 }];
            const new_link_url = "https://moogle.fr";

            const version_title = "My new version";
            const version_changelog = "Changed the version because...";
            const is_version_locked = true;

            postLinkVersion.and.throwError("nope");

            await createNewLinkVersionFromModal(context, [
                item,
                new_link_url,
                version_title,
                version_changelog,
                is_version_locked
            ]);

            expect(postLinkVersion).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("error/setModalError", jasmine.anything());
        });
    });

    describe("cancelFolderUpload", () => {
        let folder, item, context;

        beforeEach(() => {
            folder = {
                title: "My folder",
                id: 123
            };

            item = {
                parent_id: folder.id,
                is_uploading_new_version: false,
                uploader: {
                    abort: jasmine.createSpy("abort")
                }
            };

            context = {
                commit: jasmine.createSpy("commit"),
                state: {
                    files_uploads_list: [item]
                }
            };
        });

        it("should cancel the uploads of all the files being uploaded in the given folder.", async () => {
            await cancelFolderUpload(context, folder);

            expect(item.uploader.abort).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("removeItemFromFolderContent", item);
            expect(context.commit).toHaveBeenCalledWith("removeFileFromUploadsList", item);

            expect(context.commit).toHaveBeenCalledWith("resetFolderIsUploading", folder);
        });

        it("should cancel the new version uploads of files being updated in the given folder.", async () => {
            item.is_uploading_new_version = true;

            await cancelFolderUpload(context, folder);

            expect(item.uploader.abort).toHaveBeenCalled();
            expect(context.commit).not.toHaveBeenCalledWith("removeItemFromFolderContent", item);
            expect(context.commit).not.toHaveBeenCalledWith("removeFileFromUploadsList", item);

            expect(context.commit).toHaveBeenCalledWith("removeVersionUploadProgress", item);

            expect(context.commit).toHaveBeenCalledWith("resetFolderIsUploading", folder);
        });
    });

    describe("loadDocumentWithAscendentHierarchy", () => {
        it("loads ascendant hierarchy and content of item", async () => {
            const current_folder = {
                id: 3,
                title: "Project Documentation",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            const item = {
                id: 42,
                title: "My embedded file",
                owner: {
                    id: 101,
                    display_name: "user (login)"
                },
                last_update_date: "2018-08-21T17:01:49+02:00"
            };

            context.state.current_folder = current_folder;

            getItem.and.returnValue(Promise.resolve(item));
            loadFolderContent.and.returnValue(Promise.resolve(current_folder));

            await loadDocumentWithAscendentHierarchy(42);
            expect(loadAscendantHierarchy).toHaveBeenCalled();
        });

        it("throw error if something went wrong", async () => {
            getItem.and.throwError("nope");

            await loadDocumentWithAscendentHierarchy(context, 42);
            expect(loadAscendantHierarchy).not.toHaveBeenCalled();

            expect(context.commit).toHaveBeenCalledWith(
                "error/setItemLoadingError",
                "Internal server error"
            );
        });

        it("throw error permission error if user does not have enough permissions", async () => {
            mockFetchError(getItem, {
                status: 403
            });

            await loadDocumentWithAscendentHierarchy(context, 42);
            expect(loadAscendantHierarchy).not.toHaveBeenCalled();

            expect(context.commit).toHaveBeenCalledWith("error/switchItemPermissionError");
        });

        it("throw translated exceptions", async () => {
            mockFetchError(getItem, {
                status: 400,
                error_json: {
                    error: {
                        i18n_error_message: "My translated error"
                    }
                }
            });

            await loadDocumentWithAscendentHierarchy(context, 42);
            expect(loadAscendantHierarchy).not.toHaveBeenCalled();

            expect(context.commit).toHaveBeenCalledWith(
                "error/setItemLoadingError",
                "My translated error"
            );
        });

        it("throw internal server error if something bad happens", async () => {
            mockFetchError(getItem, {
                status: 400,
                response: {}
            });

            await loadDocumentWithAscendentHierarchy(context, 42);
            expect(loadAscendantHierarchy).not.toHaveBeenCalled();

            expect(context.commit).toHaveBeenCalledWith(
                "error/setItemLoadingError",
                "Internal server error"
            );
        });
    });

    describe("deleteItem()", () => {
        let item_to_delete,
            context,
            deleteFile,
            deleteLink,
            deleteEmbeddedFile,
            deleteWiki,
            deleteFolder,
            deleteEmptyDocument;

        beforeEach(() => {
            item_to_delete = {
                id: 123,
                title: "My file",
                type: TYPE_FILE
            };

            context = {
                state: {
                    folder_content: [item_to_delete],
                    currently_previewed_item: null
                },
                commit: jasmine.createSpy("commit")
            };

            deleteFile = jasmine.createSpy("deleteItem");
            rewire$deleteFile(deleteFile);

            deleteLink = jasmine.createSpy("deleteLink");
            rewire$deleteLink(deleteLink);

            deleteEmbeddedFile = jasmine.createSpy("deleteEmbeddedFile");
            rewire$deleteEmbeddedFile(deleteEmbeddedFile);

            deleteWiki = jasmine.createSpy("deleteWiki");
            rewire$deleteWiki(deleteWiki);

            deleteFolder = jasmine.createSpy("deleteFolder");
            rewire$deleteFolder(deleteFolder);

            deleteEmptyDocument = jasmine.createSpy("deleteEmptyDocument");
            rewire$deleteEmptyDocument(deleteEmptyDocument);
        });

        it("when item is a file, then the delete file route is called", async () => {
            const file_item = {
                id: 111,
                title: "My File",
                type: TYPE_FILE
            };

            await deleteItem(context, [file_item]);
            expect(deleteFile).toHaveBeenCalledWith(file_item);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                file_item
            );
        });

        it("when item is a link, then the delete link route is called", async () => {
            const link_item = {
                id: 222,
                title: "My Link",
                type: TYPE_LINK
            };

            await deleteItem(context, [link_item]);
            expect(deleteLink).toHaveBeenCalledWith(link_item);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                link_item
            );
        });

        it("when item is an embedded file, then the delete embedded file route is called", async () => {
            const embedded_file_item = {
                id: 222,
                title: "My embedded file",
                type: TYPE_EMBEDDED
            };

            await deleteItem(context, [embedded_file_item]);
            expect(deleteEmbeddedFile).toHaveBeenCalledWith(embedded_file_item);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                embedded_file_item
            );
        });

        it("when item is a wiki, then the delete wiki route is called", async () => {
            const wiki_item = {
                id: 222,
                title: "My Wiki",
                type: TYPE_WIKI
            };

            const additional_options = { delete_associated_wiki_page: true };

            await deleteItem(context, [wiki_item, additional_options]);
            expect(deleteWiki).toHaveBeenCalledWith(wiki_item, additional_options);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                wiki_item
            );
        });

        it("when item is an empty document, then the delete empty document route is called", async () => {
            const empty_doc_item = {
                id: 222,
                title: "My empty document",
                type: TYPE_EMPTY
            };

            await deleteItem(context, [empty_doc_item]);
            expect(deleteEmptyDocument).toHaveBeenCalledWith(empty_doc_item);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                empty_doc_item
            );
        });

        it("when item is a folder, then the delete folder route is called", async () => {
            const folder_item = {
                id: 222,
                title: "My folder",
                type: TYPE_FOLDER
            };

            const additional_options = { delete_associated_wiki_page: true };

            await deleteItem(context, [folder_item, additional_options]);
            expect(deleteFolder).toHaveBeenCalledWith(folder_item, additional_options);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                folder_item
            );
        });

        it("deletes the given item and removes it from the tree view", async () => {
            await deleteItem(context, [item_to_delete]);

            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                item_to_delete
            );
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                item_to_delete
            );
        });

        it("resets currentlyPreviewedItem when it references the deleted item", async () => {
            context.state.currently_previewed_item = item_to_delete;

            await deleteItem(context, [item_to_delete]);

            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                item_to_delete
            );
            expect(context.commit).toHaveBeenCalledWith("updateCurrentlyPreviewedItem", null);
            expect(context.commit).toHaveBeenCalledWith(
                "clipboard/emptyClipboardAfterItemDeletion",
                item_to_delete
            );
        });

        it("display erorr if something wrong happens", async () => {
            const folder_item = {
                id: 222,
                title: "My folder",
                type: TYPE_FOLDER
            };

            context.state.currently_previewed_item = item_to_delete;

            mockFetchError(deleteFolder, {
                status: 400
            });

            await deleteItem(context, [folder_item]);

            expect(context.commit).toHaveBeenCalledWith(
                "error/setModalError",
                "Internal server error"
            );
        });

        it("mark item as unknown when rest route fails with 404", async () => {
            const folder_item = {
                id: 222,
                title: "My folder",
                type: TYPE_FOLDER
            };

            context.state.currently_previewed_item = item_to_delete;

            mockFetchError(deleteFolder, {
                error_json: {
                    error: {
                        code: 404,
                        i18n_error_message: "not found"
                    }
                }
            });

            await deleteItem(context, [folder_item]);

            expect(context.commit).toHaveBeenCalledWith("error/setModalError", "not found");
            expect(context.commit).toHaveBeenCalledWith("removeItemFromFolderContent", folder_item);
            expect(context.commit).toHaveBeenCalledWith("updateCurrentlyPreviewedItem", null);
        });
    });

    describe("getWikisReferencingSameWikiPage()", () => {
        let getItemsReferencingSameWikiPage,
            getParents,
            context = {};

        beforeEach(() => {
            getItemsReferencingSameWikiPage = jasmine.createSpy("getItemsReferencingSameWikiPage");
            rewire$getItemsReferencingSameWikiPage(getItemsReferencingSameWikiPage);

            getParents = jasmine.createSpy("getParents");
            rewire$getParents(getParents);
        });

        it("it should return a collection of the items referencing the same wiki page", async () => {
            const wiki_1 = {
                item_name: "wiki 1",
                item_id: 1
            };

            const wiki_2 = {
                item_name: "wiki 2",
                item_id: 2
            };

            getItemsReferencingSameWikiPage.and.returnValue([wiki_1, wiki_2]);

            getParents.withArgs(wiki_1.item_id).and.returnValue(
                Promise.resolve([
                    {
                        title: "Project documentation"
                    }
                ])
            );

            getParents.withArgs(wiki_2.item_id).and.returnValue(
                Promise.resolve([
                    {
                        title: "Project documentation"
                    },
                    {
                        title: "Folder 1"
                    }
                ])
            );

            const target_wiki = {
                title: "wiki 3",
                wiki_properties: {
                    page_name: "A wiki page",
                    page_id: 123
                }
            };

            const referencers = await getWikisReferencingSameWikiPage(context, target_wiki);

            expect(referencers).toEqual([
                {
                    path: "/Project documentation/wiki 1",
                    id: 1
                },
                {
                    path: "/Project documentation/Folder 1/wiki 2",
                    id: 2
                }
            ]);
        });

        it("it should return null if there is a rest exception", async () => {
            const wiki_1 = {
                item_name: "wiki 1",
                item_id: 1
            };

            const wiki_2 = {
                item_name: "wiki 2",
                item_id: 2
            };

            getItemsReferencingSameWikiPage.and.returnValue([wiki_1, wiki_2]);
            getParents.and.returnValue(Promise.reject(500));

            const target_wiki = {
                title: "wiki 3",
                wiki_properties: {
                    page_name: "A wiki page",
                    page_id: 123
                }
            };

            const referencers = await getWikisReferencingSameWikiPage(context, target_wiki);

            expect(referencers).toEqual(null);
        });
    });

    describe("lock", () => {
        let postLockFile, postLockEmbedded, getItem, context;

        beforeEach(() => {
            context = { commit: jasmine.createSpy("commit") };

            postLockFile = jasmine.createSpy("postLockFile");
            rewire$postLockFile(postLockFile);

            postLockEmbedded = jasmine.createSpy("postLockEmbedded");
            rewire$postLockEmbedded(postLockEmbedded);

            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);
        });

        it("it should lock a file and then update its information", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_FILE
            };

            getItem.and.returnValue(
                Promise.resolve({
                    id: 123,
                    title: "My file",
                    type: TYPE_FILE,
                    lock_info: {
                        user_id: 123
                    }
                })
            );

            await lockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith("replaceLockInfoWithNewVersion", [
                item_to_lock,
                { user_id: 123 }
            ]);
        });

        it("it should raise a translated exception when user can't lock a document", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_FILE
            };

            mockFetchError(postLockFile, {
                status: 400,
                error_json: {
                    error: {
                        i18n_error_message: "Item is already locked"
                    }
                }
            });

            await lockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith(
                "error/setLockError",
                "Item is already locked"
            );
        });

        it("it should raise a translated exception when user can't lock a document", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_FILE
            };

            mockFetchError(postLockFile, {
                status: 400
            });

            await lockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith(
                "error/setLockError",
                "Internal server error"
            );
        });

        it("it should lock an embedded file and then update its information", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_EMBEDDED
            };

            getItem.and.returnValue(
                Promise.resolve({
                    id: 123,
                    title: "My embedded",
                    type: TYPE_EMBEDDED,
                    lock_info: {
                        user_id: 123
                    }
                })
            );

            await lockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith("replaceLockInfoWithNewVersion", [
                item_to_lock,
                { user_id: 123 }
            ]);
        });
    });

    describe("unlock", () => {
        let deleteLockFile, deleteLockEmbedded, getItem, context;

        beforeEach(() => {
            context = { commit: jasmine.createSpy("commit") };

            deleteLockFile = jasmine.createSpy("deleteLockFile");
            rewire$deleteLockFile(deleteLockFile);

            deleteLockEmbedded = jasmine.createSpy("deleteLockEmbedded");
            rewire$deleteLockEmbedded(deleteLockEmbedded);

            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);
        });

        it("it should unlock a file and then update its information", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_FILE
            };

            getItem.and.returnValue(
                Promise.resolve({
                    id: 123,
                    title: "My file",
                    type: TYPE_FILE,
                    lock_info: {
                        user_id: 123
                    }
                })
            );

            await unlockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith("replaceLockInfoWithNewVersion", [
                item_to_lock,
                { user_id: 123 }
            ]);
        });

        it("it should unlock an embedded file and then update its information", async () => {
            const item_to_lock = {
                id: 123,
                title: "My file",
                type: TYPE_EMBEDDED
            };

            getItem.and.returnValue(
                Promise.resolve({
                    id: 123,
                    title: "My embedded",
                    type: TYPE_EMBEDDED,
                    lock_info: {
                        user_id: 123
                    }
                })
            );

            await unlockDocument(context, item_to_lock);

            expect(context.commit).toHaveBeenCalledWith("replaceLockInfoWithNewVersion", [
                item_to_lock,
                { user_id: 123 }
            ]);
        });
    });

    describe("displayEmbeddedInLargeMode", () => {
        let setNarrowModeForEmbeddedDisplay, context;

        beforeEach(() => {
            context = {
                state: {
                    user_id: 102,
                    project_id: 110
                },
                commit: jasmine.createSpy("commit")
            };

            setNarrowModeForEmbeddedDisplay = jasmine.createSpy("setNarrowModeForEmbeddedDisplay");
            rewire$setNarrowModeForEmbeddedDisplay(setNarrowModeForEmbeddedDisplay);
        });

        it("it should store in user preferences the new mode and then update the store value", async () => {
            const item = {
                id: 123,
                title: "My embedded"
            };

            await displayEmbeddedInLargeMode(context, item);

            expect(context.commit).toHaveBeenCalledWith("shouldDisplayEmbeddedInLargeMode", true);
        });
    });

    describe("displayEmbeddedInNarrowMode", () => {
        let removeUserPreferenceForEmbeddedDisplay, context;

        beforeEach(() => {
            context = {
                state: {
                    user_id: 102,
                    project_id: 110
                },
                commit: jasmine.createSpy("commit")
            };

            removeUserPreferenceForEmbeddedDisplay = jasmine.createSpy(
                "removeUserPreferenceForEmbeddedDisplay"
            );
            rewire$removeUserPreferenceForEmbeddedDisplay(removeUserPreferenceForEmbeddedDisplay);
        });

        it("it should store in user preferences the new mode and then update the store value", async () => {
            const item = {
                id: 123,
                title: "My embedded"
            };

            await displayEmbeddedInNarrowMode(context, item);

            expect(context.commit).toHaveBeenCalledWith("shouldDisplayEmbeddedInLargeMode", false);
        });
    });

    describe("replaceMetadataWithUpdatesOnes", () => {
        let putFileMetadata,
            putEmbeddedFileMetadata,
            putLinkMetadata,
            putWikiMetadata,
            putEmptyDocumentMetadata,
            getItem,
            context;

        beforeEach(() => {
            context = {
                commit: jasmine.createSpy("commit"),
                dispatch: jasmine.createSpy("dispatch")
            };

            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);
        });

        describe("Given item is not the current folder - ", () => {
            it("it should update file metadata", async () => {
                putFileMetadata = jasmine.createSpy("putFileMetadata");
                rewire$putFileMetadata(putFileMetadata);

                const item = {
                    id: 123,
                    title: "My file",
                    type: TYPE_FILE,
                    description: "n",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new title",
                    description: "My description",
                    owner: {
                        id: 102
                    },
                    status: "draft",
                    obsolescence_date: null,
                    metadata: []
                };

                const current_folder = {
                    id: 456
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });
            it("it should update embedded file metadata", async () => {
                putEmbeddedFileMetadata = jasmine.createSpy("putEmbeddedFileMetadata");
                rewire$putEmbeddedFileMetadata(putEmbeddedFileMetadata);

                const item = {
                    id: 123,
                    title: "My embedded file",
                    type: TYPE_EMBEDDED,
                    description: "nop",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new embedded  title",
                    description: "My description",
                    owner: {
                        id: 102
                    },
                    status: "draft",
                    obsolescence_date: null,
                    metadata: []
                };

                const current_folder = {
                    id: 456
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });
            it("it should update link document metadata", async () => {
                putLinkMetadata = jasmine.createSpy("putEmbeddedLinkMetadata");
                rewire$putLinkMetadata(putLinkMetadata);
                const item = {
                    id: 123,
                    title: "My link",
                    type: TYPE_LINK,
                    description: "ui",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new link title",
                    description: "My link description",
                    owner: {
                        id: 102
                    },
                    status: "draft",
                    obsolescence_date: null,
                    metadata: []
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                const current_folder = {
                    id: 456
                };

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });

            it("it should update wiki document metadata", async () => {
                putWikiMetadata = jasmine.createSpy("putEmbeddedWikiMetadata");
                rewire$putWikiMetadata(putWikiMetadata);
                const item = {
                    id: 123,
                    title: "My wiki",
                    type: TYPE_WIKI,
                    description: "on",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new wiki title",
                    description: "My wiki description",
                    owner: {
                        id: 102
                    },
                    status: "approved",
                    obsolescence_date: null,
                    metadata: []
                };

                const current_folder = {
                    id: 456
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });
            it("it should update empty document metadata", async () => {
                putEmptyDocumentMetadata = jasmine.createSpy("putEmptyDocumentMetadata");
                rewire$putEmptyDocumentMetadata(putEmptyDocumentMetadata);
                const item = {
                    id: 123,
                    title: "My empty",
                    type: TYPE_EMPTY,
                    description: "on",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new empty title",
                    description: "My empty description",
                    owner: {
                        id: 102
                    },
                    status: "rejected",
                    obsolescence_date: null,
                    metadata: []
                };

                const current_folder = {
                    id: 456
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });

            it("it should update folder metadata", async () => {
                putEmptyDocumentMetadata = jasmine.createSpy("putEmptyDocumentMetadata");
                rewire$putEmptyDocumentMetadata(putEmptyDocumentMetadata);
                const item = {
                    id: 123,
                    title: "My folder",
                    type: TYPE_FOLDER,
                    description: "on",
                    owner: {
                        id: 102
                    }
                };

                const item_to_update = {
                    id: 123,
                    title: "My new empty title",
                    description: "My empty description",
                    owner: {
                        id: 102
                    },
                    metadata: [
                        {
                            short_name: "status",
                            list_value: [
                                {
                                    id: 103
                                }
                            ]
                        }
                    ],
                    status: {
                        value: "rejected",
                        recursion: "none"
                    }
                };

                const current_folder = {
                    id: 456
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateFolderMetadata(context, [
                    item,
                    item_to_update,
                    current_folder,
                    [],
                    "none"
                ]);

                expect(context.commit).toHaveBeenCalledWith(
                    "removeItemFromFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "addJustCreatedItemToFolderContent",
                    item_to_update
                );
                expect(context.commit).toHaveBeenCalledWith(
                    "updateCurrentItemForQuickLokDisplay",
                    item_to_update
                );
            });
        });

        describe("Given I'm updating current folder - ", () => {
            it("it should update file metadata", async () => {
                putFileMetadata = jasmine.createSpy("putFileMetadata");
                rewire$putFileMetadata(putFileMetadata);

                const item = {
                    id: 123,
                    title: "My folder",
                    type: TYPE_FOLDER,
                    description: "n",
                    owner: {
                        id: 102
                    },
                    status: "none",
                    obsolescence_date: null
                };

                const item_to_update = {
                    id: 123,
                    title: "My new title",
                    description: "My description",
                    owner: {
                        id: 102
                    },
                    status: "draft",
                    obsolescence_date: null,
                    metadata: []
                };

                const current_folder = {
                    id: 123
                };

                getItem.and.returnValue(Promise.resolve(item_to_update));

                await updateMetadata(context, [item, item_to_update, current_folder]);

                expect(context.commit).toHaveBeenCalledWith("replaceCurrentFolder", item_to_update);
                expect(context.dispatch).toHaveBeenCalledWith("loadFolder", current_folder.id);
            });
        });
    });

    describe("UpdatePermissions()", () => {
        const permissions = {
            can_read: [],
            can_write: [],
            can_manage: []
        };

        let getItem, context;

        beforeEach(() => {
            context = {
                commit: jasmine.createSpy("commit"),
                dispatch: jasmine.createSpy("dispatch"),
                state: {
                    current_folder: { id: 999, type: TYPE_FOLDER }
                }
            };

            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);
        });

        afterEach(() => {
            restoreHandleErrors();
        });

        const testPermissionsUpdateSuccess = async type => {
            const item = {
                id: 123,
                type: type
            };

            getItem.and.returnValue(Promise.resolve(item));

            await updatePermissions(context, [item, permissions]);
        };

        it("Can update file permissions", async () => {
            const putFilePermissions = jasmine.createSpy("putFilePermissions");
            rewire$putFilePermissions(putFilePermissions);

            await testPermissionsUpdateSuccess(TYPE_FILE);

            expect(putFilePermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update embedded file permissions", async () => {
            const putEmbeddedFilePermissions = jasmine.createSpy("putEmbeddedFilePermissions");
            rewire$putEmbeddedFilePermissions(putEmbeddedFilePermissions);

            await testPermissionsUpdateSuccess(TYPE_EMBEDDED);

            expect(putEmbeddedFilePermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update link permissions", async () => {
            const putLinkPermissions = jasmine.createSpy("putLinkPermissions");
            rewire$putLinkPermissions(putLinkPermissions);

            await testPermissionsUpdateSuccess(TYPE_LINK);

            expect(putLinkPermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update wiki permissions", async () => {
            const putWikiPermissions = jasmine.createSpy("putWikiPermissions");
            rewire$putWikiPermissions(putWikiPermissions);

            await testPermissionsUpdateSuccess(TYPE_WIKI);

            expect(putWikiPermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update empty document permissions", async () => {
            const putEmptyDocumentPermissions = jasmine.createSpy("putEmptyDocumentPermissions");
            rewire$putEmptyDocumentPermissions(putEmptyDocumentPermissions);

            await testPermissionsUpdateSuccess(TYPE_EMPTY);

            expect(putEmptyDocumentPermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update folder permissions", async () => {
            const putFolderPermissions = jasmine.createSpy("putFolderPermissions");
            rewire$putFolderPermissions(putFolderPermissions);

            await testPermissionsUpdateSuccess(TYPE_FOLDER);

            expect(putFolderPermissions).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                jasmine.any(Object)
            );
            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                jasmine.any(Object)
            );
        });

        it("Can update folder permissions when it is the current folder", async () => {
            const putFolderPermissions = jasmine.createSpy("putFolderPermissions");
            rewire$putFolderPermissions(putFolderPermissions);

            const folder = { id: 123, type: TYPE_FOLDER };
            context.state.current_folder = folder;

            getItem.and.returnValue(Promise.resolve(folder));

            await updatePermissions(context, [folder, permissions]);

            expect(putFolderPermissions).toHaveBeenCalled();
            expect(context.dispatch).toHaveBeenCalledWith("loadFolder", folder.id);
            expect(context.commit).toHaveBeenCalledWith(
                "replaceCurrentFolder",
                jasmine.any(Object)
            );
        });

        it("Set an error in modal when is raised while updating permissions", async () => {
            const putEmptyDocumentPermissions = jasmine.createSpy("putEmptyDocumentPermissions");
            rewire$putEmptyDocumentPermissions(putEmptyDocumentPermissions);
            mockFetchError(putEmptyDocumentPermissions, {
                status: 500
            });
            const handleErrorsModal = jasmine.createSpy("handleErrorsModal");
            rewire$handleErrorsForModal(handleErrorsModal);

            await updatePermissions(context, [{ id: 123, type: TYPE_EMPTY }, permissions]);

            expect(getItem).not.toHaveBeenCalled();
            expect(handleErrorsModal).toHaveBeenCalled();
        });
    });

    describe("loadProjectUserGroupsIfNeeded", () => {
        it("Retrieve the project user groups when they are never been loaded", async () => {
            const getProjectUserGroupsWithoutServiceSpecialUGroupsSpy = jasmine.createSpy(
                "getProjectUserGroupsWithoutServiceSpecialUGroups"
            );
            rewire$getProjectUserGroupsWithoutServiceSpecialUGroups(
                getProjectUserGroupsWithoutServiceSpecialUGroupsSpy
            );
            const project_ugroups = [{ id: "102_3", label: "Project members" }];
            getProjectUserGroupsWithoutServiceSpecialUGroupsSpy.and.returnValue(
                Promise.resolve(project_ugroups)
            );

            context.state.project_ugroups = null;

            await loadProjectUserGroupsIfNeeded(context);

            expect(getProjectUserGroupsWithoutServiceSpecialUGroupsSpy).toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith("setProjectUserGroups", project_ugroups);
        });

        it("Does not retrieve the project user groups when they have already been retrieved", async () => {
            const getProjectUserGroupsWithoutServiceSpecialUGroupsSpy = jasmine.createSpy(
                "getProjectUserGroupsWithoutServiceSpecialUGroups"
            );
            rewire$getProjectUserGroupsWithoutServiceSpecialUGroups(
                getProjectUserGroupsWithoutServiceSpecialUGroupsSpy
            );

            context.state.project_ugroups = [{ id: "102_3", label: "Project members" }];

            await loadProjectUserGroupsIfNeeded(context);

            expect(getProjectUserGroupsWithoutServiceSpecialUGroupsSpy).not.toHaveBeenCalled();
        });
    });

    describe("toggleQuickLook", () => {
        let getItem, context;

        beforeEach(() => {
            context = { commit: jasmine.createSpy("commit") };
            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);
        });

        afterEach(() => {
            restoreHandleErrors();
        });

        it("should load item and store it as open in quick look", async () => {
            const item = {
                id: 123,
                title: "My file",
                type: TYPE_FILE,
                description: "n",
                owner: {
                    id: 102
                },
                status: "none",
                obsolescence_date: null
            };

            getItem.and.returnValue(Promise.resolve(item));

            await toggleQuickLook(context, [item]);

            expect(context.commit).toHaveBeenCalledWith("updateCurrentlyPreviewedItem", item);
            expect(context.commit).toHaveBeenCalledWith("toggleQuickLook", true);
        });
    });
    describe("createNewVersionFromEmpty - ", () => {
        let postNewLinkVersionFromEmpty,
            postNewEmbeddedFileVersionFromEmpty,
            getItem,
            context,
            handleErrorsForModal;
        beforeEach(() => {
            handleErrorsForModal = jasmine.createSpy("handleErrorsForModal");
            rewire$handleErrorsForModal(handleErrorsForModal);

            postNewLinkVersionFromEmpty = jasmine.createSpy("postNewLinkVersionFromEmpty");
            rewire$postNewLinkVersionFromEmpty(postNewLinkVersionFromEmpty);

            postNewEmbeddedFileVersionFromEmpty = jasmine.createSpy(
                "postNewEmbeddedFileVersionFromEmpty"
            );
            rewire$postNewEmbeddedFileVersionFromEmpty(postNewEmbeddedFileVersionFromEmpty);

            getItem = jasmine.createSpy("getItem");
            rewire$getItem(getItem);

            context = {
                commit: jasmine.createSpy("commit")
            };
        });

        afterEach(() => {
            restoreRestQuerier();
        });
        it("should update the empty document to link document", async () => {
            const item_to_update = {
                type: TYPE_EMPTY,
                link_properties: {
                    link_url: "https://example.test"
                }
            };
            const item = {
                id: 123,
                type: TYPE_EMPTY
            };

            const updated_item = {
                id: 123,
                type: TYPE_LINK
            };
            getItem.and.returnValue(Promise.resolve(updated_item));

            await createNewVersionFromEmpty(context, [TYPE_LINK, item, item_to_update]);

            expect(postNewLinkVersionFromEmpty).toHaveBeenCalled();
            expect(postNewEmbeddedFileVersionFromEmpty).not.toHaveBeenCalled();
            expect(handleErrorsForModal).not.toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                updated_item
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                updated_item
            );

            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                updated_item
            );
        });

        it("should update the empty document to embedded_file document", async () => {
            const item_to_update = {
                type: TYPE_EMPTY,
                embedded_properties: {
                    content: "content"
                }
            };
            const item = {
                id: 123,
                type: TYPE_EMPTY
            };

            const updated_item = {
                id: 123,
                type: TYPE_EMBEDDED
            };

            getItem.and.returnValue(Promise.resolve(updated_item));

            await createNewVersionFromEmpty(context, [TYPE_EMBEDDED, item, item_to_update]);

            expect(postNewLinkVersionFromEmpty).not.toHaveBeenCalled();
            expect(postNewEmbeddedFileVersionFromEmpty).toHaveBeenCalled();
            expect(handleErrorsForModal).not.toHaveBeenCalled();
            expect(context.commit).toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                updated_item
            );
            expect(context.commit).toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                updated_item
            );

            expect(context.commit).toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                updated_item
            );
        });

        it("should failed the update", async () => {
            const item_to_update = {
                type: TYPE_EMPTY,
                link_properties: {
                    link_url: "https://example.test"
                }
            };
            const item = {
                id: 123,
                type: TYPE_EMPTY
            };

            const updated_item = {
                id: 123,
                type: TYPE_LINK
            };

            postNewLinkVersionFromEmpty.and.throwError("Failed to update");

            await createNewVersionFromEmpty(context, [TYPE_LINK, item, item_to_update]);

            expect(postNewLinkVersionFromEmpty).toHaveBeenCalled();
            expect(handleErrorsForModal).toHaveBeenCalled();
            expect(getItem).not.toHaveBeenCalled();
            expect(context.commit).not.toHaveBeenCalledWith(
                "removeItemFromFolderContent",
                updated_item
            );
            expect(context.commit).not.toHaveBeenCalledWith(
                "addJustCreatedItemToFolderContent",
                updated_item
            );

            expect(context.commit).not.toHaveBeenCalledWith(
                "updateCurrentItemForQuickLokDisplay",
                updated_item
            );
        });
    });
});
