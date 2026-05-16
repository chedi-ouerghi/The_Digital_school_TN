import { StyleSheet, Dimensions } from 'react-native';

const { width: screenWidth, height: screenHeight } = Dimensions.get('window');

export const luxuryColors = {
  black: '#000000',
  charcoal: '#1a1a1a',
  darkGray: '#2a2a2a',
  gold: '#D4AF37',
  lightGold: '#FFD700',
  darkGold: '#B8860B',
  white: '#FFFFFF',
  lightGray: '#f5f5f5',
  silver: '#C0C0C0',
  accent: 'rgba(212, 175, 55, 0.8)',
};

export const luxuryStyles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: luxuryColors.black,
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: luxuryColors.black,
  },
  loadingText: {
    color: luxuryColors.gold,
    fontSize: 18,
    fontFamily: 'Inter_400Regular',
  },
  scrollView: {
    flex: 1,
  },
  
  // Header Styles
  header: {
    position: 'absolute',
    top: 0,
    left: 0,
    right: 0,
    zIndex: 1000,
    height: 100,
  },
  headerGradient: {
    flex: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-end',
    paddingHorizontal: 20,
    paddingBottom: 15,
  },
  headerLogo: {
    color: luxuryColors.gold,
    fontSize: 24,
    fontFamily: 'PlayfairDisplay_700Bold',
    letterSpacing: 2,
  },
  cartButton: {
    paddingHorizontal: 15,
    paddingVertical: 8,
    borderWidth: 1,
    borderColor: luxuryColors.gold,
    borderRadius: 20,
  },
  cartText: {
    color: luxuryColors.white,
    fontSize: 14,
    fontFamily: 'Inter_400Regular',
  },

  // Hero Section
  heroSection: {
    height: screenHeight,
    position: 'relative',
  },
  heroBackground: {
    width: '100%',
    height: '100%',
    position: 'absolute',
  },
  heroOverlay: {
    position: 'absolute',
    width: '100%',
    height: '100%',
  },
  maskContainer: {
    position: 'absolute',
    width: '100%',
    height: '100%',
  },
  heroContainer: {
    height: screenHeight,
  },
  heroContent: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    paddingHorizontal: 20,
  },
  heroTitle: {
    fontSize: 72,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: luxuryColors.gold,
    textAlign: 'center',
    letterSpacing: 8,
    marginBottom: 10,
  },
  heroSubtitle: {
    fontSize: 24,
    fontFamily: 'Inter_300Light',
    color: luxuryColors.white,
    textAlign: 'center',
    letterSpacing: 3,
    marginBottom: 20,
  },
  heroDescription: {
    fontSize: 16,
    fontFamily: 'Inter_400Regular',
    color: luxuryColors.lightGray,
    textAlign: 'center',
    marginBottom: 40,
    lineHeight: 24,
  },
  featuredProduct: {
    width: 250,
    height: 150,
    marginTop: 30,
  },
  productImage: {
    width: '100%',
    height: '100%',
  },

  // Story Section
  storySection: {
    paddingHorizontal: 20,
    paddingVertical: 80,
    backgroundColor: luxuryColors.charcoal,
  },
  sectionTitle: {
    fontSize: 36,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: luxuryColors.gold,
    textAlign: 'center',
    marginBottom: 30,
    letterSpacing: 2,
  },
  storyText: {
    fontSize: 18,
    fontFamily: 'Inter_400Regular',
    color: luxuryColors.lightGray,
    textAlign: 'center',
    lineHeight: 28,
    maxWidth: 600,
    alignSelf: 'center',
  },

  // Products Section
  productsSection: {
    paddingHorizontal: 20,
    paddingVertical: 60,
    backgroundColor: luxuryColors.black,
  },
  productCard: {
    marginBottom: 30,
    borderRadius: 15,
    overflow: 'hidden',
    boxShadow: '0 10px 30px rgba(212, 175, 55, 0.1)',
    elevation: 8,
  },
  cardGradient: {
    borderRadius: 15,
    overflow: 'hidden',
    borderWidth: 1,
    borderColor: 'rgba(212, 175, 55, 0.2)',
  },
  productImageContainer: {
    height: 200,
    position: 'relative',
  },
  cardProductImage: {
    width: '100%',
    height: '100%',
  },
  imageOverlay: {
    position: 'absolute',
    bottom: 0,
    left: 0,
    right: 0,
    height: 60,
  },
  productInfo: {
    padding: 20,
  },
  productName: {
    fontSize: 24,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: luxuryColors.white,
    marginBottom: 8,
  },
  productDescription: {
    fontSize: 14,
    fontFamily: 'Inter_400Regular',
    color: luxuryColors.lightGray,
    marginBottom: 15,
    lineHeight: 20,
  },
  priceContainer: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  productPrice: {
    fontSize: 28,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: luxuryColors.gold,
  },
  addToCartButton: {
    backgroundColor: luxuryColors.gold,
    paddingHorizontal: 20,
    paddingVertical: 10,
    borderRadius: 25,
  },
  addToCartText: {
    color: luxuryColors.black,
    fontSize: 14,
    fontFamily: 'Inter_600SemiBold',
  },

  // CTA Section
  ctaSection: {
    marginHorizontal: 20,
    marginVertical: 40,
    borderRadius: 20,
    overflow: 'hidden',
  },
  ctaGradient: {
    paddingVertical: 60,
    paddingHorizontal: 30,
    alignItems: 'center',
  },
  ctaTitle: {
    fontSize: 32,
    fontFamily: 'PlayfairDisplay_700Bold',
    color: luxuryColors.black,
    textAlign: 'center',
    marginBottom: 15,
  },
  ctaSubtitle: {
    fontSize: 16,
    fontFamily: 'Inter_400Regular',
    color: luxuryColors.charcoal,
    textAlign: 'center',
    marginBottom: 30,
  },
  ctaButton: {
    backgroundColor: luxuryColors.black,
    paddingHorizontal: 40,
    paddingVertical: 15,
    borderRadius: 30,
  },
  ctaButtonText: {
    color: luxuryColors.gold,
    fontSize: 16,
    fontFamily: 'Inter_600SemiBold',
    letterSpacing: 1,
  },

  // Footer
  footer: {
    paddingVertical: 40,
    paddingHorizontal: 20,
    backgroundColor: luxuryColors.charcoal,
    alignItems: 'center',
  },
  footerText: {
    color: luxuryColors.lightGray,
    fontSize: 14,
    fontFamily: 'Inter_400Regular',
    marginBottom: 5,
  },
  footerSubtext: {
    color: luxuryColors.gold,
    fontSize: 12,
    fontFamily: 'Inter_400Regular',
    letterSpacing: 1,
  },
});