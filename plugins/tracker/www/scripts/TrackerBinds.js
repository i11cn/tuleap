/**
 * Copyright (c) Enalean, 2017. All Rights Reserved.
 * Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
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

/* global Class:readonly $:readonly Sortable:readonly tuleap:readonly */

var codendi = codendi || {};
codendi.tracker = codendi.tracker || {};

codendi.tracker.bind = {};

codendi.tracker.bind.Editor = Class.create({
    initialize: function(element) {
        if (!element) {
            element = document.body;
        }

        $(element)
            .select('input[type=text][name^="bind[edit]"]')
            .each(this.editStaticValues.bind(this));
        $(element)
            .select(".tracker-admin-bindvalue_list")
            .each(this.sortFieldStaticValues.bind(this));
        this.accordionForBindTypes();
        this.addNew();
    },
    /**
     * wrap a text to a fixed width
     *
     * @param string text       the text to wrap
     * @param int    line_width the width of the paragraph
     * @param string separator  The line separator \n, <br />, ...
     *
     * @return string
     */
    wordwrap: function(text, line_width, sep) {
        var space_left = line_width;
        var s = [];
        text.split(" ").each(function(word) {
            if (word.length > space_left) {
                s.push(sep + word);
                space_left = line_width - word.length;
            } else {
                s.push(word);
                space_left = space_left - (word.length + 1);
            }
        });
        return s.join(" ");
    },
    nl2br: function(str) {
        return str.replace("/\n/g", "<br />");
    },
    //hide the textarea and textfield which update description and label of the value
    //replace them by a link. If the user click on the link, hide the link and show the fields
    editStaticValues: function(element) {
        var tf_label = element;
        var ta_description = element.up().down("textarea");
        var link = new Element("a", { href: "#", title: "Edit " + tf_label.value }).update(
            tuleap.escaper.html(tf_label.value)
        );
        var descr = new Element("div")
            .addClassName("tracker-admin-bindvalue_description")
            .update(this.nl2br(tuleap.escaper.html(this.wordwrap(ta_description.value, 80, "\n"))));
        tf_label.insert({ before: link });
        link.insert({ after: descr });
        link.observe("click", function(evt) {
            link.hide();
            descr.hide();
            tf_label.show();
            ta_description.show();
            evt.stop();
        });
        tf_label.hide();
        ta_description.hide();
    },

    setValuesOrderField: function(list) {
        list.up("form").down(".bind_order_values").value = Sortable.sequence(list).join(",");
    },

    fixWidthOfDefaultValuesSelectbox: function(list) {
        var new_width = list.getWidth();

        list.up("form")
            .down(".bind_default_values")
            .setStyle({
                width: new_width + "px"
            });
    },

    sortFieldStaticValues: function(list) {
        var checkbox_rank_alpha = list.up("form").down(".is_rank_alpha");

        this.fixWidthOfDefaultValuesSelectbox(list);

        checkbox_rank_alpha.observe(
            "click",
            function(evt) {
                if (!Event.element(evt).checked) {
                    return;
                }

                list.childElements()
                    .sortBy(function(li) {
                        return li.down(".tracker-admin-bindvalue_label input[type=text]").value;
                    })
                    .each(function(li) {
                        list.appendChild(li);
                    });
                this.setValuesOrderField(list);
            }.bind(this)
        );

        Sortable.create(list, {
            handle: "tracker-admin-bindvalue_grip",
            onUpdate: function() {
                checkbox_rank_alpha.checked = false;
                this.setValuesOrderField(list);
            }.bind(this)
        });
    },

    accordionForBindTypes: function() {
        if ($("tracker-bind-factory")) {
            $("tracker-bind-factory")
                .select('input[name="formElement_data[bind-type]"]')
                .each(function(selector) {
                    selector.observe("click", function() {
                        if (this.checked) {
                            this.up("#tracker-bind-factory")
                                .select(".tracker-bind-def")
                                .invoke("hide");
                            this.up(".tracker-bind-type")
                                .next(".tracker-bind-def")
                                .show();
                        }
                    });
                });
            $("tracker-bind-factory")
                .select(".tracker-bind-def")
                .invoke("hide");
            (
                $("tracker-bind-factory").down(
                    'input[name="formElement_data[bind-type]"][checked="checked"]'
                ) || $("tracker-bind-factory").down('input[name="formElement_data[bind-type]"]')
            )
                .up(".tracker-bind-type")
                .next(".tracker-bind-def")
                .show();
        }
    },
    addNew: function() {
        var el = $("tracker-admin-bind-static-addnew");
        if (el) {
            var label = el.down().innerHTML;
            el.insert({
                before: new Element("a", {
                    href: "#",
                    title: label
                })
                    .update('<img src="' + codendi.imgroot + 'ic/add.png" /> ' + label)
                    .observe("click", function(evt) {
                        this.hide();
                        el.show();
                        evt.stop();
                    })
            });
            el.hide();
        }
    }
});

document.observe("dom:loaded", function() {
    //eslint-disable-next-line @typescript-eslint/no-unused-vars
    var e = new codendi.tracker.bind.Editor();
});
