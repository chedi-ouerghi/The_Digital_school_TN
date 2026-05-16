import { Platform, SafeAreaView } from 'react-native';
import { useEffect, useState } from 'react';
import { Stack, useGlobalSearchParams } from 'expo-router';
import { commonStyles } from '../styles/commonStyles';
import { StatusBar } from 'expo-status-bar';
import { setupErrorLogging } from '../utils/errorLogger';
import { SafeAreaProvider, useSafeAreaInsets } from 'react-native-safe-area-context';
import { CartProvider } from '../context/CartContext';

const RootLayout = () => {
  return (
    <CartProvider>
      <SafeAreaProvider>
        <StatusBar style="auto" />
        <Stack screenOptions={{ headerShown: false }}>
          <Stack.Screen name="(tabs)" />
        </Stack>
      </SafeAreaProvider>
    </CartProvider>
  );
};

export default RootLayout;
