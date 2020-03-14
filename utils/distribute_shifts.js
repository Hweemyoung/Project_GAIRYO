class InputHandler {
    constructor() {
        this.$inputNumItems = $("#input-num-items");
        this.$query1 = $("#query-1");
        this.$btnAddItem = $("#btn-add-item");
        this.$defaultQueryItem = $(".query-item").clone();
        this.numItems = 0;

        // Add event
        this.$btnAddItem.click($.proxy(this.addFormItem, this));

        // Add item number
        // this.addItemNumber($(".query-item"), 1);
        // Remove query-item
        $(".query-item").remove();
        // Add value to #input-num-items
        this.$inputNumItems.attr("value", 0);
    }

    addItemNumber($queryItem, num) {
        console.log("adding number...");
        $.each($queryItem.find("input"), function () {
            var name = $(this).attr("name");
            console.log("name of input before:", name);
            $(this).attr("name", `${String(num)}_${name}`);
            console.log("name of input after:", $(this).attr("name"));
        })
        return $queryItem;
    }

    addFormItem(event) {
        this.numItems++;
        var $newQueryItem = this.$defaultQueryItem.clone();
        this.addItemNumber($newQueryItem, this.numItems);
        this.$query1.append($newQueryItem);
        this.$inputNumItems.attr("value", this.numItems);
    }
}

var input_handler = new InputHandler();