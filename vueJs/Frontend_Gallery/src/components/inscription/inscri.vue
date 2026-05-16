<template>
  <div class="container">
    <form @submit.prevent="formSubmit">
      <div class="row">
        <h4>Account</h4>
        <div class="error">{{ v$?.fullName?.$error }}</div>
        <div class="input-group input-group-icon">
          <input type="text" placeholder="Full Name" v-model="fullName" @blur="v$.fullName.$touch()" />
          <div class="input-icon"><i class="fa fa-user"></i></div>
        </div>
        <div class="error">{{ v$?.email?.$error }}</div>
        <div class="input-group input-group-icon">
          <input type="email" placeholder="Email Address" v-model="email" @blur="v$.email.$touch()" />
          <div class="input-icon"><i class="fa fa-envelope"></i></div>
        </div>
        <div class="error">{{ v$?.password?.$error }}</div>
        <div class="input-group input-group-icon">
          <input type="password" placeholder="Password" v-model="password" @blur="v$.password.$touch()" />
          <div class="input-icon"><i class="fa fa-key"></i></div>
        </div>
        <div class="error">{{ v$?.fullName?.$error }}</div>
      </div>
      <div class="row">
        <div class="col-half">
          <h4>Date of Birth</h4>
          <div class="input-group">
            <div class="col-third">
              <input type="number" placeholder="DD" v-model.number="birth.day" @blur="v$.birth.day.$touch()" />
            </div>
            <div class="col-third">
              <input type="number" placeholder="MM" v-model.number="birth.month" @blur="v$.birth.month.$touch()" />
            </div>
            <div class="col-third">
              <input type="number" placeholder="YYYY" v-model.number="birth.year" @blur="v$.birth.year.$touch()" />
            </div>
          </div>
          <div class="error">{{ v$?.birth?.$error }}</div>
        </div>
        <div class="col-half">
          <h4>Gender</h4>
          <div class="input-group">
            <input type="radio" name="gender" value="male" id="gender-male" v-model="gender" />
            <label for="gender-male">Male</label>
            <input type="radio" name="gender" value="female" id="gender-female" v-model="gender" />
            <label for="gender-female">Female</label>
          </div>
          <div class="error">{{ v$?.gender?.$error }}</div>
        </div>
      </div>
      <div class="row">
        <h4>Payment</h4>
        <div class="input-group input-group-icon">
          <input type="number" placeholder="Card Number" v-model.number="cardNumber" @blur="v$.cardNumber.$touch()" />
          <div class="input-icon"><i class="fa fa-credit-card"></i></div>
        </div>
        <div class="error">{{ v$?.cardNumber?.$error }}</div>
        <div class="col-half">
          <div class="input-group input-group-icon">
            <input type="number" placeholder="Card CVC" v-model.number="cardCVC" @blur="v$.cardCVC.$touch()" />
            <div class="input-icon"><i class="fa fa-user"></i></div>
          </div>
          <div class="error">{{ v$?.cardCVC?.$error }}</div>
        </div>
        <div class="col-half">
          <div class="input-group">
            <select v-model="expiryMonth" @blur="v$.expiryMonth.$touch()">
              <option value="1">01</option>
              <option value="2">02</option>
              <!-- autres options -->
            </select>
            <select v-model="expiryYear" @blur="v$.expiryYear.$touch()">
              <option value="2020">2020</option>
              <option value="2021">2021</option>
              <!-- autres options -->
            </select>
          </div>
          <div class="error">{{ v$?.expiryMonth?.$error }}</div>
          <div class="error">{{ v$?.expiryYear?.$error }}</div>
        </div>
      </div>
      <div class="row">
        <h4>Terms and Conditions</h4>
        <div class="input-group">
          <input type="checkbox" id="terms" v-model="acceptTerms" @blur="v$.acceptTerms.$touch()" />
          <label for="terms">I accept the terms and conditions for signing up to this service, and hereby confirm I have read the privacy policy.</label>
        </div>
        <div class="error">{{ v$?.acceptTerms?.$error }}</div>
      </div>
      <div class="row">
        <button type="submit">Save</button>
      </div>
    </form>
  </div>
</template>

<script>
import { required, minLength, email as emailValidator, numeric } from '@vuelidate/validators';
import useVuelidate from '@vuelidate/core';
import { reactive, toRefs } from 'vue';

export default {
  name: 'inscri',
  setup() {
    const state = reactive({
      fullName: '',
      email: '',
      password: '',
      birth: {
        day: '',
        month: '',
        year: ''
      },
      gender: '',
      cardNumber: '',
      cardCVC: '',
      expiryMonth: '',
      expiryYear: '',
      acceptTerms: false
    });

    const rules = {
      fullName: { required, minLength: minLength(6) },
      email: { required, email: emailValidator },
      password: { required, minLength: minLength(6), alphaNumeric: /^[a-zA-Z0-9]*$/ },
      'birth.day': { required, numeric },
      'birth.month': { required, numeric },
      'birth.year': { required, numeric },
      cardNumber: { required, minLength: minLength(16), numeric },
      cardCVC: { required, minLength: minLength(3), numeric },
      acceptTerms: { required }
    };

    const v$ = useVuelidate(rules, state);

    const formSubmit = () => {
      v$.value.$touch();
      if (!v$.value.$invalid) {
        console.log('Form submitted successfully!');
        console.log('Data:', {
          fullName: state.fullName,
          email: state.email,
          password: state.password,
          birth: state.birth,
          gender: state.gender,
          cardNumber: state.cardNumber,
          cardCVC: state.cardCVC,
          expiryMonth: state.expiryMonth,
          expiryYear: state.expiryYear,
          acceptTerms: state.acceptTerms
        });
        // Add further logic for submission (e.g., API call)
      } else {
        console.log('Validation errors.');
      }
    };

    return {
      ...toRefs(state),
      v$,
      formSubmit
    };
  }
};
</script>

<style src="./style.css" />
