import { submit } from './submit.js';

function moveAddSubscriberButton() {
    const button = document.querySelector(
        'body.edit-php.post-type-subscriber .wrap > .page-title-action'
    );
    const statusTabs = document.querySelector(
        'body.edit-php.post-type-subscriber .subsubsub'
    );

    if (!button || !statusTabs) {
        return;
    }

    const tabsItem = document.createElement('li');
    const tabsList = document.createElement('ul');
    tabsItem.className = 'sk-subscriber-tabs';

    while (statusTabs.firstChild) {
        tabsList.append(statusTabs.firstChild);
    }

    tabsItem.append(tabsList);
    statusTabs.append(tabsItem);

    const actionRow = document.createElement('li');
    actionRow.className = 'sk-subscriber-add-action';
    actionRow.append(button);
    statusTabs.append(actionRow);
}

export function subscriberAdmin() {
    moveAddSubscriberButton();
    submit();
}
