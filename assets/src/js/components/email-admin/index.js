function moveAddEmailButton() {
    const button = document.querySelector(
        'body.edit-php.post-type-mailing .wrap > .page-title-action'
    );
    const statusTabs = document.querySelector(
        'body.edit-php.post-type-mailing .subsubsub'
    );

    if (!button || !statusTabs) {
        return;
    }

    const tabsItem = document.createElement('li');
    const tabsList = document.createElement('ul');
    tabsItem.className = 'sk-email-tabs';

    while (statusTabs.firstChild) {
        tabsList.append(statusTabs.firstChild);
    }

    tabsItem.append(tabsList);
    statusTabs.append(tabsItem);

    const actionRow = document.createElement('li');
    actionRow.className = 'sk-email-add-action';
    actionRow.append(button);
    statusTabs.append(actionRow);
}

export function emailAdmin() {
    moveAddEmailButton();
}
