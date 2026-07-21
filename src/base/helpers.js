import { Repeater } from "./fields/repeater/repeater";
import { ButtonModal } from "./fields/button/button";
import { Text } from "./fields/text/text";
import { TextArea } from "./fields/textarea/textarea";
import { Image } from "./fields/image/image";
import { Gradient } from "./fields/color-gradient/color-gradient";
import { Background } from "./fields/background/background";
import { Media } from "./fields/media/media";
import { Radio } from "./fields/radio/radio";
import { Object } from "./fields/object/object";

// const Map = {
//     select: <TreeSelect />,
//     image: <MediaUploadCheck />
// }

export const getField = (type, index, value, setAttributes , values) => {
    // console.log('map', Map[type]);

    switch (type) {
        case 'select':
            return (
                <TreeSelect/>
            );
        case 'image': 
            return <Image imageUrl={value} index={index} setAttributes={setAttributes} />;
            
        case 'button':
            return <ButtonModal />;
        case 'link':
            return <Link>link</Link>;
        case 'file':
            return <FormFileUpload>file</FormFileUpload>;

        case 'text': return <Text value={value} index={index} setAttributes={setAttributes} />;

        case 'textarea': return <TextArea attributes={value} index={index} setAttributes={setAttributes} />;
        
        case 'color':
            return <PaletteEdit/>;
        case 'editor':
            return <Editor/>;
        case 'date':
            return <Date/>;
        case 'checkbox':
            return <CheckboxControl></CheckboxControl>;
        case 'radio':
            return <Radio attributes={value} label={index} values={values} setAttributes={setAttributes} />;
        case 'editorbox':
            return <EditorBox/>;
        case 'repeater':
            return <Repeater/>;
        case 'gradient':
            return <Gradient gradientData={value} label={index} setAttributes={setAttributes} />;
        case 'background':
            return <Background attributes={value} setAttributes={setAttributes} />;
        case 'media':
            return <Media attributes={value} setAttributes={setAttributes} />;
        case 'object':
            return <Object attributes={value} index={index} setAttributes={setAttributes} />;
        default:
            return null;
    }
};
