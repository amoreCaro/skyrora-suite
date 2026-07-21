import { registerBlockType } from '@wordpress/blocks';
import Block from './block.json';
import Edit from './edit';
import Save from './save';
import '/src/base/styles/index.scss';

registerBlockType(Block.name, {
    edit: Edit,
    save: Save
});