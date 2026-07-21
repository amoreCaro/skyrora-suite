import { GradientPicker } from '@wordpress/components';

export const Gradient = ({ gradientData, setAttributes }) => {

    return(
        <div className='app-group'>
            <GradientPicker
                value={ gradientData.gradientValue }
                gradients={[]}
                onChange={(currentGradient) => {
                    setAttributes({ gradientValue: currentGradient });
                }}            
            />
        </div>
    );
}