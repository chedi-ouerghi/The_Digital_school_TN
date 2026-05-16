import React, { useRef, useState } from "react";
import {useFormik} from 'formik';
import './ContactForm.css';

const ContactForm = () => {
    const messageRef = useRef();
    const [formData, setFormData] = useState({
        firstName: "",
        lastName: "",
        email: "",
        password: "",
        message: ""
    });

    const onUpdateData = event => {
        const target = event.target,
            value = target.value,
            name = target.name;

        const data = { formData };
        data[name] = value;
        setFormData(data);
    };

    const onSubmit = (event) => {
        event.preventDefault();

        if (formData.message.includes('shit') || formData.message.includes('fuck')) {
            messageRef.current.style.borderColor = 'red';
        } else {
            console.log(formData);
        }
    }


    
    return (
        <div className="wrapper">
            <form onSubmit={onSubmit}>
                <div className="title">Contact Us</div>
                <div className="name-wrapper">
                    <input
                        type="text"
                        name="firstName"
                        placeholder="First Name"
                        value={formData.firstName}
                        onChange={onUpdateData}
                        autoFocus
                        required
                    />
                    <input
                        type="text"
                        name="lastName"
                        placeholder="Last Name"
                        value={formData.lastName}
                        onChange={onUpdateData}
                        autoFocus
                        required
                    />
                </div>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value={formData.email}
                    onChange={onUpdateData}
                    autoFocus
                    required
                />
                
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    value={formData.password}
                    onChange={onUpdateData}
                    autoFocus
                    required
                />

                <textarea
                    name="message"
                    placeholder="Message"
                    value={formData.message}
                    onChange={onUpdateData}
                    autoFocus
                    required
                    ref={messageRef}
                />
                <button id="submit" name="submit" type="submit"></button>
            </form>
        </div>
    )
}

export default ContactForm;