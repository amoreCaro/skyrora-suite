
import { Button } from '@wordpress/components';

export const previewTabs = ({data}) => {
    const activeTab = data.activeTab;

    const getSliderPosition = (activeTab) => {
        const index = data.tabList.findIndex((tab) => tab.key === activeTab);
        return index !== -1 ? index * 60 : 0;
    };
    
    const handleTabClick = (tab) => {
        data.setActiveTab(tab);
    };

    const renderTabContent = () => {
        return data.tabList.map((tab) => (
            activeTab === tab.key && (
                <div key={tab.key} className="tabs-content__item active">
                    {data[tab.key]()}
                </div>
            )
        ));
    };
   
    return (
        <div className="tabs tabs--vertical">
            <div className="tabs-navigation" style={{ position: 'relative' }}>
                {data.tabList.map((tab) => (
                    <Button
                        key={tab.key}
                        className={`tabs-navigation__item ${activeTab === tab.key ? 'active' : ''}`}
                        onClick={() => handleTabClick(tab.key)}
                    >
                        {tab.label}
                    </Button>
                ))}
                <div
                    className="tabs-slider"
                    style={{
                        top: `${getSliderPosition(activeTab)}px`,
                        position: 'absolute',
                        transition: 'top 0.3s ease',
                    }}
                />
            </div>
            <div className="tabs-content">
                {renderTabContent()}
            </div>
        </div>
    );
};
