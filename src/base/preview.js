import { 
    useEffect, 
    useState 
} from '@wordpress/element';

import {  
    BlockControls,
} from '@wordpress/block-editor';

import { 
    Button ,
    ToolbarGroup
} from '@wordpress/components';


export const renderPreview = ({ data }) => {

    const [ isLoaded, setIsLoaded ] = useState(false);

    useEffect(() => {

        const timeout = setTimeout(() => {
            setIsLoaded(true); 
        }, 1500); 

        return () => clearTimeout(timeout);

    }, []);
  
    return (
        <>
            <BlockControls>
                <ToolbarGroup>
                    <span>{data.blockName}</span>
                </ToolbarGroup>
            </BlockControls>

            {isLoaded ? (
                <div className='app-block app-block--preview app-block--preview-full'>
                    <div className='app-block__header'>
                        <div className='app-block__group'>
                            <div className='app-block__index'>
                                1
                            </div>
                            <div className='app-block__name'>
                                Block - {data.blockName}
                            </div>
                        </div>
                        <div className='app-block__group app-block__group--actions'>
                            <Button 
                                className='app-icon -view app-js-tooltip' 
                                onClick={data.handleViewBlock}
                                type='button' 
                                title='View Block'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 24 24" fill="none" stroke="#000000" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="feather feather-eye"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </Button>
                            <Button 
                                onClick={data.handleDeleteBlock}
                                className='app-icon -minus app-js-tooltip' 
                                type='button' 
                                title='Remove Block'>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20px" height="20px" viewBox="0 0 24 24" fill="none" stroke="#000000" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="feather feather-minus-circle"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            </Button>
                        </div>
                    </div>
                    <div className='app-block__body'>
                        <Button 
                            className='btn-none btn--edit-block' 
                            onClick={() => data.openModal()} 
                            type='button' 
                            title='Edit block'>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="#000000" width="16px" height="16px" viewBox="-2.5 -2.5 24 24" preserveAspectRatio="xMinYMin" className="jam jam-pencil"><path d="M12.238 5.472L3.2 14.51l-.591 2.016 1.975-.571 9.068-9.068-1.414-1.415zM13.78 3.93l1.414 1.414 1.318-1.318a.5.5 0 0 0 0-.707l-.708-.707a.5.5 0 0 0-.707 0L13.781 3.93zm3.439-2.732l.707.707a2.5 2.5 0 0 1 0 3.535L5.634 17.733l-4.22 1.22a1 1 0 0 1-1.237-1.241l1.248-4.255 12.26-12.26a2.5 2.5 0 0 1 3.535 0z"/></svg>
                        </Button>
                        
                        {data.defaultPreview()}
                        
                    </div>
                </div>
            ) : (
                <div className={`app-block app-block--preview app-block--preview-full`}>
                    <div className={`app-block__header skeleton-header`}>
                        <div className="app-block__group">
                            <div className={`app-block__index skeleton`}>
                            </div>
                            <div className={`app-block__name skeleton`}>
                            </div>
                        </div>
                        <div className={`app-block__group app-block__group--actions skeleton`}>
                        </div>
                    </div>
                    <div className={`app-block__body skeleton-body skeleton-bg`}>
                        <button className={`btn-none skeleton`}>
                        </button>
                    </div>
                </div>
            )}
        </>
    );
};
