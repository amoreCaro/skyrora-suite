import { useState } from "react";
import { 
    Button, 
    Modal
} from '@wordpress/components';

export const ButtonModal = ({data}) => {

    const [isModalOpen, setIsModalOpen] = useState(false);

    const openModal = () => setIsModalOpen(true);
    const closeModal = () => setIsModalOpen(false);

    const saveButtonDetails = () => {

        setAttributes(
            data.buttonName,
            data.buttonUrl,
            data.buttonOpenInNewTab,
        );

        closeModal();
    };

    return (
        <>
            <Button className="app-button" onClick={openModal}>
                Open Button
            </Button>

            {data.isModalOpen && (
                <Modal title="Button Details" onRequestClose={closeModal}>
                    <div>
                        <label>
                            Button Name:
                            <input
                                type="text"
                                placeholder="Enter button name"
                            />
                        </label>
                        <label>
                            Button URL:
                            <input
                                type="text"
                                placeholder="Enter button URL"
                            />
                        </label>
                        <label>
                            <input
                                type="checkbox"
                            />
                            Open in new tab
                        </label>
                    </div>
                    <Button onClick={saveButtonDetails}>Save</Button>
                    <Button onClick={closeModal}>Cancel</Button>
                </Modal>
            )}
        </>
    );
};